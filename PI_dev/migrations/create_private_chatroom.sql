-- Create private_chatroom table
CREATE TABLE IF NOT EXISTS private_chatroom (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    parent_goal_id INT NOT NULL,
    creator_id INT NOT NULL,
    CONSTRAINT fk_private_chatroom_goal FOREIGN KEY (parent_goal_id) REFERENCES goal(id),
    CONSTRAINT fk_private_chatroom_creator FOREIGN KEY (creator_id) REFERENCES "user"(id)
);

-- Create private_chatroom_members table
CREATE TABLE IF NOT EXISTS private_chatroom_members (
    private_chatroom_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (private_chatroom_id, user_id),
    CONSTRAINT fk_pcm_chatroom FOREIGN KEY (private_chatroom_id) REFERENCES private_chatroom(id) ON DELETE CASCADE,
    CONSTRAINT fk_pcm_user FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
);

-- Add foreign key constraint for message.private_chatroom_id
ALTER TABLE message 
ADD CONSTRAINT fk_message_private_chatroom 
FOREIGN KEY (private_chatroom_id) REFERENCES private_chatroom(id);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_private_chatroom_goal ON private_chatroom(parent_goal_id);
CREATE INDEX IF NOT EXISTS idx_private_chatroom_creator ON private_chatroom(creator_id);
CREATE INDEX IF NOT EXISTS idx_pcm_chatroom ON private_chatroom_members(private_chatroom_id);
CREATE INDEX IF NOT EXISTS idx_pcm_user ON private_chatroom_members(user_id);
CREATE INDEX IF NOT EXISTS idx_message_private_chatroom ON message(private_chatroom_id);
