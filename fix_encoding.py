#!/usr/bin/env python3
# Fix broken UTF-8 encoding in twig and php files
# The broken chars result from UTF-16 -> UTF-8 conversion gone wrong

import os
import sys

def fix_encoding(content_bytes):
    """
    Fix double/wrong encoded UTF-8 characters.
    The bytes E2 94 9C followed by C2 XX are broken UTF-8 sequences
    that should be single UTF-8 accented chars.
    
    E2 94 9C = U+251C = ├ (box drawing)
    C2 AE = U+00AE = ®
    Together ├® in the source file = bytes E2 94 9C C2 AE
    But this should be é = C3 A9
    
    The pattern: take the byte sequence, decode as latin-1, 
    re-encode as utf-8 to get back the original.
    """
    # Try to decode as utf-8 first
    try:
        text = content_bytes.decode('utf-8')
    except UnicodeDecodeError:
        return content_bytes  # Can't process
    
    # Check if the text contains box-drawing characters (sign of broken encoding)
    if '\u251c' not in text and '\u2500' not in text:
        return content_bytes  # Already clean
    
    # The text was originally UTF-8, then the bytes were read as latin-1 (windows-1252),
    # then re-encoded as UTF-8. So we reverse: decode UTF-8 to get the wrong unicode,
    # encode as latin-1 to get the intermediate bytes, then decode as UTF-8 to get original.
    try:
        # Encode the broken unicode string back to bytes using latin-1
        intermediate = text.encode('latin-1', errors='replace')
        # Now decode those bytes as UTF-8 to get the original text
        fixed_text = intermediate.decode('utf-8', errors='replace')
        return fixed_text.encode('utf-8')
    except Exception as e:
        print(f"  Error during fix: {e}")
        return content_bytes

def process_directory(base_dir):
    extensions = ('.twig', '.php')
    fixed_count = 0
    
    for root, dirs, files in os.walk(base_dir):
        # Skip vendor and cache directories
        dirs[:] = [d for d in dirs if d not in ('vendor', 'cache', 'node_modules', '.git')]
        
        for filename in files:
            if not filename.endswith(extensions):
                continue
            
            filepath = os.path.join(root, filename)
            
            try:
                with open(filepath, 'rb') as f:
                    original = f.read()
                
                fixed = fix_encoding(original)
                
                if fixed != original:
                    with open(filepath, 'wb') as f:
                        f.write(fixed)
                    rel_path = os.path.relpath(filepath, base_dir)
                    print(f"Fixed: {rel_path}")
                    fixed_count += 1
                    
            except Exception as e:
                print(f"Error processing {filepath}: {e}")
    
    return fixed_count

if __name__ == '__main__':
    base = r'c:\piDev\Projet-PI-2026\PI_dev'
    
    print("Scanning templates...")
    count1 = process_directory(os.path.join(base, 'templates'))
    print(f"\nScanning src...")
    count2 = process_directory(os.path.join(base, 'src'))
    
    total = count1 + count2
    print(f"\nDone! Fixed {total} files total.")
