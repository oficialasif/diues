-- DIU ESPORTS COMMUNITY DATABASE SCHEMA (PostgreSQL)
-- Create this database in PostgreSQL before running the application

-- Users table for admin authentication
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'admin' CHECK (role IN ('admin', 'moderator')),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Committee Members table
CREATE TABLE committee_members (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    image_url VARCHAR(255),
    bio TEXT,
    achievements TEXT,
    social_links JSONB,
    is_current BOOLEAN DEFAULT TRUE,
    year INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Games table
CREATE TABLE games (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    genre VARCHAR(50) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tournaments table
CREATE TABLE tournaments (
    id SERIAL PRIMARY KEY,
    game_id INTEGER NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    poster_url VARCHAR(255),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    prize_pool DECIMAL(10,2),
    max_participants INTEGER,
    current_participants INTEGER DEFAULT 0,
    status VARCHAR(20) DEFAULT 'upcoming' CHECK (status IN ('upcoming', 'ongoing', 'completed', 'cancelled')),
    results JSONB,
    highlights_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Tournament Registrations table
CREATE TABLE tournament_registrations (
    id SERIAL PRIMARY KEY,
    tournament_id INTEGER NOT NULL,
    team_name VARCHAR(100) NOT NULL,
    team_type VARCHAR(10) NOT NULL CHECK (team_type IN ('solo', 'duo', 'squad')),
    captain_name VARCHAR(100) NOT NULL,
    captain_email VARCHAR(100) NOT NULL,
    captain_phone VARCHAR(20),
    captain_discord VARCHAR(100),
    captain_student_id VARCHAR(50),
    captain_department VARCHAR(100),
    captain_semester VARCHAR(20),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (tournament_id) REFERENCES tournaments(id) ON DELETE CASCADE
);

-- Tournament Team Members table
CREATE TABLE tournament_team_members (
    id SERIAL PRIMARY KEY,
    registration_id INTEGER NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    player_email VARCHAR(100),
    player_phone VARCHAR(20),
    player_discord VARCHAR(100),
    player_student_id VARCHAR(50),
    player_department VARCHAR(100),
    player_semester VARCHAR(20),
    player_role VARCHAR(20) DEFAULT 'member' CHECK (player_role IN ('captain', 'member', 'substitute')),
    game_username VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES tournament_registrations(id) ON DELETE CASCADE
);

-- Events table
CREATE TABLE events (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    poster_url VARCHAR(255),
    event_date TIMESTAMP NOT NULL,
    location VARCHAR(200),
    event_type VARCHAR(20) NOT NULL CHECK (event_type IN ('tournament', 'meetup', 'workshop', 'celebration')),
    is_featured BOOLEAN DEFAULT FALSE,
    status VARCHAR(20) DEFAULT 'upcoming' CHECK (status IN ('upcoming', 'ongoing', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Gallery table
CREATE TABLE gallery (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    video_url VARCHAR(255),
    category VARCHAR(20) NOT NULL CHECK (category IN ('tournament', 'event', 'achievement', 'community')),
    year INTEGER NOT NULL,
    tags JSONB,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Achievements table
CREATE TABLE achievements (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(20) NOT NULL CHECK (category IN ('tournament', 'individual', 'team', 'community')),
    year INTEGER NOT NULL,
    icon_url VARCHAR(255),
    highlights_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hall of Fame table
CREATE TABLE hall_of_fame (
    id SERIAL PRIMARY KEY,
    member_name VARCHAR(100) NOT NULL,
    achievement VARCHAR(200) NOT NULL,
    game VARCHAR(100),
    year INTEGER NOT NULL,
    image_url VARCHAR(255),
    stats JSONB,
    highlights_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sponsors table
CREATE TABLE sponsors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    category VARCHAR(100),
    partnership_type VARCHAR(20) NOT NULL CHECK (partnership_type IN ('platinum', 'gold', 'silver', 'bronze')),
    website_url VARCHAR(255),
    benefits TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- About Us content table
CREATE TABLE about_content (
    id SERIAL PRIMARY KEY,
    section_name VARCHAR(100) NOT NULL,
    title VARCHAR(200),
    content TEXT NOT NULL,
    image_url VARCHAR(255),
    order_index INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site Settings table
CREATE TABLE site_settings (
    id SERIAL PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Event Countdown Settings table
CREATE TABLE event_countdown_settings (
    id SERIAL PRIMARY KEY,
    status_text VARCHAR(200) NOT NULL,
    custom_message TEXT,
    target_date TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    show_countdown BOOLEAN DEFAULT TRUE,
    countdown_type VARCHAR(20) DEFAULT 'days' CHECK (countdown_type IN ('days', 'hours', 'minutes')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create triggers for updated_at timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply triggers to all tables with updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_committee_members_updated_at BEFORE UPDATE ON committee_members FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_games_updated_at BEFORE UPDATE ON games FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_tournaments_updated_at BEFORE UPDATE ON tournaments FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_events_updated_at BEFORE UPDATE ON events FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_gallery_updated_at BEFORE UPDATE ON gallery FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_achievements_updated_at BEFORE UPDATE ON achievements FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_hall_of_fame_updated_at BEFORE UPDATE ON hall_of_fame FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_sponsors_updated_at BEFORE UPDATE ON sponsors FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_about_content_updated_at BEFORE UPDATE ON about_content FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_site_settings_updated_at BEFORE UPDATE ON site_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_event_countdown_settings_updated_at BEFORE UPDATE ON event_countdown_settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- Insert default admin user (password: admin*diuEsports)
INSERT INTO users (username, email, password_hash, role) VALUES 
('asifmahmud', 'asifmahmud@diu.edu.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert default games
INSERT INTO games (name, genre, description, is_active) VALUES 
('Valorant', 'FPS', 'Tactical shooter game', TRUE),
('PUBG Mobile', 'Battle Royale', 'Mobile battle royale game', TRUE),
('Free Fire', 'Battle Royale', 'Mobile battle royale game', TRUE),
('Call of Duty Mobile', 'FPS', 'Mobile first-person shooter', TRUE),
('Mobile Legends', 'MOBA', 'Mobile multiplayer online battle arena', TRUE),
('FIFA Mobile', 'Sports', 'Mobile football simulation game', TRUE),
('Clash Royale', 'Strategy', 'Real-time strategy game', TRUE);

-- Insert default about content
INSERT INTO about_content (section_name, title, content, order_index) VALUES 
('mission', 'Our Mission', 'To foster a competitive gaming environment that promotes teamwork, strategic thinking, and sportsmanship while building a strong esports community at DIU.', 1),
('vision', 'Our Vision', 'To become the leading university esports community in Bangladesh, recognized for excellence in competitive gaming and community building.', 2),
('values', 'Core Values', 'Excellence, Teamwork, Innovation, Integrity, and Community Spirit', 3);

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value, description) VALUES 
('site_title', 'DIU ESPORTS COMMUNITY', 'Main website title'),
('site_description', 'Professional esports community at Daffodil International University', 'Website description'),
('contact_email', 'esports@diu.edu.bd', 'Primary contact email'),
('contact_phone', '+880 1234-567890', 'Primary contact phone'),
('address', 'Daffodil International University, Dhaka, Bangladesh', 'Physical address'),
('social_discord', 'https://discord.gg/diuesports', 'Discord server link'),
('social_twitch', 'https://twitch.tv/diuesports', 'Twitch channel'),
('social_facebook', 'https://facebook.com/diuesports', 'Facebook page'),
('social_youtube', 'https://youtube.com/diuesports', 'YouTube channel');

-- Create indexes for better performance
CREATE INDEX idx_tournaments_game_id ON tournaments(game_id);
CREATE INDEX idx_tournaments_status ON tournaments(status);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_gallery_category ON gallery(category);
CREATE INDEX idx_committee_current ON committee_members(is_current);
CREATE INDEX idx_sponsors_active ON sponsors(is_active);
