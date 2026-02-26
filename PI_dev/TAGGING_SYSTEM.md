# Automatic Tagging System Documentation

## Overview
This system automatically generates tags for posts using TF-IDF (Term Frequency-Inverse Document Frequency) algorithm. Tags help with content categorization, filtering, and discovery.

## Architecture

### Entities

#### Tag Entity
- **Fields:**
  - `id`: Primary key
  - `name`: Unique tag name (lowercase, max 100 chars)
  - `slug`: URL-friendly version
  - `usageCount`: Number of posts using this tag
  - `createdAt`: Creation timestamp
  - `posts`: ManyToMany relationship with Post

#### Post Entity (Updated)
- **New Field:**
  - `tags`: ManyToMany relationship with Tag

### Services

#### TaggingService
Located at: `src/Service/Tagging/TaggingService.php`

**Main Methods:**
- `generateTagsForPost(Post $post): array` - Generate and assign tags
- `regenerateTagsForPost(Post $post): array` - Regenerate tags for existing post
- `setMaxTags(int $maxTags): self` - Configure number of tags
- `getMaxTags(): int` - Get current max tags setting

**Internal Methods:**
- `extractText(Post $post): string` - Extract text from title + content
- `tokenize(string $text): array` - Tokenize and normalize text
- `getTermFrequency(array $tokens): array` - Calculate TF
- `getInverseDocumentFrequency(string $term): float` - Calculate IDF
- `calculateTfIdfScores(array $tokens): array` - Calculate TF-IDF
- `extractTopKeywords(array $tfIdfScores, int $limit): array` - Get top keywords

## TF-IDF Algorithm

### What is TF-IDF?
TF-IDF (Term Frequency-Inverse Document Frequency) is a numerical statistic that reflects how important a word is to a document in a collection of documents.

### Formula
```
TF-IDF(term, document) = TF(term, document) × IDF(term)

Where:
- TF(term, document) = (Number of times term appears in document) / (Total number of terms in document)
- IDF(term) = log(Total number of documents / Number of documents containing term)
```

### How It Works

1. **Text Extraction:**
   - Combines post title (weighted 2x) and content
   - Strips HTML tags from content
   - Example: `"Machine Learning" + "Machine Learning" + "Content about ML..."`

2. **Tokenization:**
   - Converts to lowercase
   - Removes punctuation and special characters
   - Splits into words
   - Filters:
     - Min length: 3 characters
     - Max length: 30 characters
     - Removes stop words (English & French)

3. **TF Calculation:**
   - Counts word occurrences
   - Divides by total words
   - Example: "machine" appears 5 times in 100 words → TF = 0.05

4. **IDF Calculation:**
   - Counts total published posts
   - Counts posts containing the term
   - Applies logarithm
   - Example: 1000 total posts, 50 contain "machine" → IDF = log(1000/50) = 3.0

5. **TF-IDF Score:**
   - Multiplies TF × IDF
   - Example: 0.05 × 3.0 = 0.15

6. **Keyword Extraction:**
   - Sorts terms by TF-IDF score (descending)
   - Selects top N terms (default: 2)

### Stop Words
The system filters out common words that don't add semantic value:
- **English:** the, be, to, of, and, a, in, that, have, etc.
- **French:** le, la, les, un, une, des, de, du, et, ou, etc.

## Configuration

### Service Configuration
File: `config/services.yaml`

```yaml
App\Service\Tagging\TaggingService:
    arguments:
        $maxTags: 2  # Number of tags to generate per post
```

### Customization Options

#### Change Number of Tags
```php
// In controller or service
$taggingService->setMaxTags(3); // Generate 3 tags instead of 2
```

#### Adjust Stop Words
Edit `TaggingService::STOP_WORDS` constant to add/remove stop words.

#### Adjust Word Length Filters
Edit constants:
- `MIN_WORD_LENGTH`: Minimum word length (default: 3)
- `MAX_WORD_LENGTH`: Maximum word length (default: 30)

## Usage

### Automatic Tagging on Post Creation

```php
use App\Service\Tagging\TaggingService;

#[Route('/posts/create', name: 'post_create')]
public function create(
    Request $request,
    PostService $postService,
    TaggingService $taggingService
): Response {
    // Create post
    $post = $postService->createPost($title, $content, $user);
    
    // Generate tags automatically
    $tags = $taggingService->generateTagsForPost($post);
    
    // $tags contains array of Tag entities
    return $this->json(['success' => true, 'tags' => $tags]);
}
```

### Regenerate Tags for Existing Post

```php
// Remove old tags and generate new ones
$tags = $taggingService->regenerateTagsForPost($post);
```

### Manual Tag Assignment

```php
use App\Repository\TagRepository;

// Find or create tag
$tag = $tagRepository->findOrCreate('symfony');

// Add to post
$post->addTag($tag);
$tag->incrementUsageCount();

$entityManager->persist($tag);
$entityManager->flush();
```

## Database Schema

### Tables Created

#### `tags`
```sql
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    slug VARCHAR(50) NOT NULL,
    usage_count INT DEFAULT 0,
    created_at DATETIME NOT NULL,
    INDEX idx_tag_name (name)
);
```

#### `post_tags` (Join Table)
```sql
CREATE TABLE post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);
```

## Migration

Run the migration to create tables:

```bash
php bin/console doctrine:migrations:migrate
```

## Examples

### Example 1: Tech Blog Post

**Input:**
- Title: "Introduction to Machine Learning"
- Content: "Machine learning is a subset of artificial intelligence that enables computers to learn from data..."

**Process:**
1. Tokens: [introduction, machine, learning, subset, artificial, intelligence, enables, computers, learn, data, ...]
2. TF-IDF Scores:
   - machine: 0.45
   - learning: 0.42
   - artificial: 0.38
   - intelligence: 0.35
   - ...

**Output Tags:** `machine`, `learning`

### Example 2: Recipe Post

**Input:**
- Title: "Chocolate Cake Recipe"
- Content: "This chocolate cake recipe is easy to make. Mix flour, sugar, cocoa powder..."

**Process:**
1. Tokens: [chocolate, cake, recipe, easy, make, mix, flour, sugar, cocoa, powder, ...]
2. TF-IDF Scores:
   - chocolate: 0.52
   - cake: 0.48
   - recipe: 0.35
   - cocoa: 0.32
   - ...

**Output Tags:** `chocolate`, `cake`

## Performance Considerations

### IDF Calculation
- Queries database for each unique term
- Cached within single request
- For better performance, consider:
  - Implementing Redis cache for IDF values
  - Pre-calculating IDF for common terms
  - Running batch updates periodically

### Optimization Tips

1. **Limit Corpus Size:**
   ```php
   // Only consider recent posts for IDF
   $totalDocs = $this->postRepository->count([
       'status' => 'published',
       'createdAt' => ['>=', new \DateTime('-1 year')]
   ]);
   ```

2. **Async Processing:**
   ```php
   // Use Symfony Messenger for async tagging
   $messageBus->dispatch(new GenerateTagsMessage($post->getId()));
   ```

3. **Batch Processing:**
   ```php
   // Tag multiple posts at once
   foreach ($posts as $post) {
       $taggingService->generateTagsForPost($post);
   }
   $entityManager->flush(); // Single flush
   ```

## Future Enhancements

### 1. AI-Based Tagging
Replace TF-IDF with machine learning models:
- Use OpenAI API for semantic tagging
- Implement BERT-based keyword extraction
- Train custom NLP model

### 2. Multi-Language Support
- Detect post language automatically
- Use language-specific stop words
- Apply language-specific stemming

### 3. Tag Suggestions
- Suggest existing popular tags to users
- Show related tags
- Auto-complete tag input

### 4. Tag Analytics
- Track tag performance
- Show trending tags
- Analyze tag co-occurrence

### 5. Tag Hierarchies
- Create parent-child tag relationships
- Implement tag synonyms
- Build tag taxonomies

## Troubleshooting

### No Tags Generated

**Possible Causes:**
1. Post content too short
2. All words are stop words
3. No unique terms found

**Solution:**
- Check logs for warnings
- Verify post has meaningful content
- Adjust MIN_WORD_LENGTH if needed

### Irrelevant Tags

**Possible Causes:**
1. Stop words list incomplete
2. TF-IDF threshold too low
3. Corpus too small

**Solution:**
- Add more stop words
- Increase MIN_WORD_LENGTH
- Wait for more posts to improve IDF

### Performance Issues

**Possible Causes:**
1. Large corpus (many posts)
2. IDF calculation for each term
3. No caching

**Solution:**
- Implement caching layer
- Use async processing
- Optimize database queries

## Testing

### Unit Test Example

```php
use App\Service\Tagging\TaggingService;
use PHPUnit\Framework\TestCase;

class TaggingServiceTest extends TestCase
{
    public function testGenerateTagsForPost(): void
    {
        $post = new Post();
        $post->setTitle('Machine Learning Tutorial');
        $post->setContent('Learn about machine learning algorithms...');
        
        $tags = $this->taggingService->generateTagsForPost($post);
        
        $this->assertCount(2, $tags);
        $this->assertContains('machine', array_map(fn($t) => $t->getName(), $tags));
    }
}
```

## API Endpoints (Future)

### Get Post Tags
```
GET /api/posts/{id}/tags
Response: ["machine", "learning"]
```

### Get Posts by Tag
```
GET /api/tags/{slug}/posts
Response: [Post[], pagination]
```

### Get Popular Tags
```
GET /api/tags/popular?limit=20
Response: [Tag[]]
```

## Conclusion

The automatic tagging system provides intelligent content categorization using proven TF-IDF algorithm. It's extensible, configurable, and ready for production use. Future enhancements can integrate AI models for even better results.
