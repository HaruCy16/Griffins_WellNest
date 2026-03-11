-- ==============================================================================
-- SEED DATA for Griffins' Wellnest Database
-- Run this AFTER schema.sql to populate initial data
-- ==============================================================================

USE griffin_wellnest_db;

-- ==============================================================================
-- CLEANUP EXISTING DATA (run in reverse order of dependencies)
-- ==============================================================================
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM audit_logs;
DELETE FROM notifications;
DELETE FROM game_progress;
DELETE FROM recommendations;
DELETE FROM assessment_scores;
DELETE FROM assessment_responses;
DELETE FROM answer_options;
DELETE FROM questions;
DELETE FROM therapeutic_games;
DELETE FROM wellness_activities;
DELETE FROM assessments;
DELETE FROM users;
DELETE FROM sections;
DELETE FROM roles;
SET FOREIGN_KEY_CHECKS = 1;

-- Reset auto-increment counters
ALTER TABLE roles AUTO_INCREMENT = 1;
ALTER TABLE sections AUTO_INCREMENT = 1;
ALTER TABLE assessments AUTO_INCREMENT = 1;
ALTER TABLE questions AUTO_INCREMENT = 1;
ALTER TABLE answer_options AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE therapeutic_games AUTO_INCREMENT = 1;
ALTER TABLE wellness_activities AUTO_INCREMENT = 1;

-- ==============================================================================
-- SEED ROLES
-- ==============================================================================
INSERT INTO roles (role_id, role_name, description, permissions) VALUES
(1, 'student', 'Student user - can take assessments and view recommendations', '["take_assessment", "view_recommendations", "play_games", "view_progress"]'),
(2, 'counselor', 'School counselor - can view student data and provide recommendations', '["view_students", "review_assessments", "add_recommendations", "view_reports", "message_students"]'),
(3, 'admin', 'System administrator - full access to all features', '["manage_users", "manage_assessments", "manage_system", "view_all_data", "manage_roles"]');

-- ==============================================================================
-- SEED SECTIONS (Sample)
-- ==============================================================================
INSERT INTO sections (section_name, grade_level, academic_year, is_active) VALUES
('Grade 11 - STEM', 11, '2025-2026', TRUE),
('Grade 11 - ABM', 11, '2025-2026', TRUE),
('Grade 11 - HUMSS', 11, '2025-2026', TRUE),
('Grade 12 - STEM', 12, '2025-2026', TRUE),
('Grade 12 - ABM', 12, '2025-2026', TRUE),
('Grade 12 - HUMSS', 12, '2025-2026', TRUE);

-- ==============================================================================
-- SEED ASSESSMENTS
-- ==============================================================================
-- Privacy notice displayed before every assessment:
-- "Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we're here to support you."

INSERT INTO assessments (assessment_name, description, assessment_type, total_questions, estimated_time, instructions, is_active) VALUES
('School Experience', 
 'Evaluate your satisfaction with school and sense of belonging in the campus community.', 
 'school_experience', 3, 5, 
 'Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we are here to support you.',
 TRUE),

('Mental Health Status', 
 'A comprehensive assessment covering positive mental health, depression screening (PHQ-9), and anxiety screening (GAD-7).', 
 'mental_health', 24, 15, 
 'Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we are here to support you.',
 TRUE),

('Help-Seeking Attitudes', 
 'Understand your experiences and attitudes toward mental health support and counseling.', 
 'help_seeking', 3, 5, 
 'Your thoughts and feelings are important to us. Please know that anything you share here is completely private and will only be accessible to the Office of the School Counselor. Your honesty is safe, and we are here to support you.',
 TRUE);

-- ==============================================================================
-- ASSESSMENT 1: SCHOOL EXPERIENCE QUESTIONS
-- ==============================================================================
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(1, 'How satisfied are you with your overall experience at school?', 'likert', 1, 'satisfaction', TRUE),
(1, 'I see myself as a part of the campus community.', 'likert', 2, 'belonging', TRUE),
(1, 'Feel free to share any thoughts or reflections about your school experience and your sense of belonging in the school community.', 'open_text', 3, 'reflection', FALSE);

-- Question 1: School satisfaction (1-6 scale)
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(1, 'Very dissatisfied', 1, 1, 'very_negative'),
(1, 'Dissatisfied', 2, 2, 'negative'),
(1, 'Somewhat dissatisfied', 3, 3, 'slightly_negative'),
(1, 'Somewhat satisfied', 4, 4, 'slightly_positive'),
(1, 'Satisfied', 5, 5, 'positive'),
(1, 'Very satisfied', 6, 6, 'very_positive');

-- Question 2: Campus belonging (1-6 scale, reversed scoring)
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(2, 'Strongly agree', 6, 1, 'very_positive'),
(2, 'Agree', 5, 2, 'positive'),
(2, 'Somewhat agree', 4, 3, 'slightly_positive'),
(2, 'Somewhat disagree', 3, 4, 'slightly_negative'),
(2, 'Disagree', 2, 5, 'negative'),
(2, 'Strongly disagree', 1, 6, 'very_negative');

-- ==============================================================================
-- ASSESSMENT 2: MENTAL HEALTH STATUS QUESTIONS
-- ==============================================================================

-- SECTION A: POSITIVE MENTAL HEALTH (Flourishing Scale - 8 questions, 1-7 scale)
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(2, 'I lead a purposeful and meaningful life.', 'likert', 1, 'positive_mental_health', TRUE),
(2, 'My social relationships are supportive and rewarding.', 'likert', 2, 'positive_mental_health', TRUE),
(2, 'I am engaged and interested in my daily activities.', 'likert', 3, 'positive_mental_health', TRUE),
(2, 'I actively contribute to the happiness and well-being of others.', 'likert', 4, 'positive_mental_health', TRUE),
(2, 'I am competent and capable in the activities that are important to me.', 'likert', 5, 'positive_mental_health', TRUE),
(2, 'I am a good person and live a good life.', 'likert', 6, 'positive_mental_health', TRUE),
(2, 'I am optimistic about my future.', 'likert', 7, 'positive_mental_health', TRUE),
(2, 'People respect me.', 'likert', 8, 'positive_mental_health', TRUE);

-- Positive Mental Health scale options (1-7) for questions 4-11
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(4, 'Strongly disagree', 1, 1, 'very_negative'),
(4, 'Disagree', 2, 2, 'negative'),
(4, 'Slightly disagree', 3, 3, 'slightly_negative'),
(4, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(4, 'Slightly agree', 5, 5, 'slightly_positive'),
(4, 'Agree', 6, 6, 'positive'),
(4, 'Strongly agree', 7, 7, 'very_positive'),

(5, 'Strongly disagree', 1, 1, 'very_negative'),
(5, 'Disagree', 2, 2, 'negative'),
(5, 'Slightly disagree', 3, 3, 'slightly_negative'),
(5, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(5, 'Slightly agree', 5, 5, 'slightly_positive'),
(5, 'Agree', 6, 6, 'positive'),
(5, 'Strongly agree', 7, 7, 'very_positive'),

(6, 'Strongly disagree', 1, 1, 'very_negative'),
(6, 'Disagree', 2, 2, 'negative'),
(6, 'Slightly disagree', 3, 3, 'slightly_negative'),
(6, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(6, 'Slightly agree', 5, 5, 'slightly_positive'),
(6, 'Agree', 6, 6, 'positive'),
(6, 'Strongly agree', 7, 7, 'very_positive'),

(7, 'Strongly disagree', 1, 1, 'very_negative'),
(7, 'Disagree', 2, 2, 'negative'),
(7, 'Slightly disagree', 3, 3, 'slightly_negative'),
(7, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(7, 'Slightly agree', 5, 5, 'slightly_positive'),
(7, 'Agree', 6, 6, 'positive'),
(7, 'Strongly agree', 7, 7, 'very_positive'),

(8, 'Strongly disagree', 1, 1, 'very_negative'),
(8, 'Disagree', 2, 2, 'negative'),
(8, 'Slightly disagree', 3, 3, 'slightly_negative'),
(8, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(8, 'Slightly agree', 5, 5, 'slightly_positive'),
(8, 'Agree', 6, 6, 'positive'),
(8, 'Strongly agree', 7, 7, 'very_positive'),

(9, 'Strongly disagree', 1, 1, 'very_negative'),
(9, 'Disagree', 2, 2, 'negative'),
(9, 'Slightly disagree', 3, 3, 'slightly_negative'),
(9, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(9, 'Slightly agree', 5, 5, 'slightly_positive'),
(9, 'Agree', 6, 6, 'positive'),
(9, 'Strongly agree', 7, 7, 'very_positive'),

(10, 'Strongly disagree', 1, 1, 'very_negative'),
(10, 'Disagree', 2, 2, 'negative'),
(10, 'Slightly disagree', 3, 3, 'slightly_negative'),
(10, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(10, 'Slightly agree', 5, 5, 'slightly_positive'),
(10, 'Agree', 6, 6, 'positive'),
(10, 'Strongly agree', 7, 7, 'very_positive'),

(11, 'Strongly disagree', 1, 1, 'very_negative'),
(11, 'Disagree', 2, 2, 'negative'),
(11, 'Slightly disagree', 3, 3, 'slightly_negative'),
(11, 'Mixed or neither agree nor disagree', 4, 4, 'neutral'),
(11, 'Slightly agree', 5, 5, 'slightly_positive'),
(11, 'Agree', 6, 6, 'positive'),
(11, 'Strongly agree', 7, 7, 'very_positive');

-- SECTION B: DEPRESSION (PHQ-9 - 9 questions, 1-4 scale)
-- Intro text: Over the last 2 weeks, how often have you been bothered by any of the following problems?
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(2, 'Little interest or pleasure in doing things', 'likert', 9, 'depression', TRUE),
(2, 'Feeling down, depressed or hopeless', 'likert', 10, 'depression', TRUE),
(2, 'Trouble falling or staying asleep, or sleeping too much', 'likert', 11, 'depression', TRUE),
(2, 'Feeling tired or having little energy', 'likert', 12, 'depression', TRUE),
(2, 'Poor appetite or overeating', 'likert', 13, 'depression', TRUE),
(2, 'Feeling bad about yourself—or that you are a failure or have let yourself or your family down', 'likert', 14, 'depression', TRUE),
(2, 'Trouble concentrating on things, such as reading the newspaper or watching television', 'likert', 15, 'depression', TRUE),
(2, 'Moving or speaking so slowly that other people could have noticed; or the opposite—being so fidgety or restless that you have been moving around a lot more than usual', 'likert', 16, 'depression', TRUE),
(2, 'Thoughts that you would be better off dead or of hurting yourself in some way', 'likert', 17, 'depression', TRUE);

-- Depression scale options (1-4) for questions 12-20
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(12, 'Not at all', 1, 1, 'positive'),
(12, 'Several days', 2, 2, 'mild'),
(12, 'More than half the days', 3, 3, 'moderate'),
(12, 'Nearly every day', 4, 4, 'severe'),

(13, 'Not at all', 1, 1, 'positive'),
(13, 'Several days', 2, 2, 'mild'),
(13, 'More than half the days', 3, 3, 'moderate'),
(13, 'Nearly every day', 4, 4, 'severe'),

(14, 'Not at all', 1, 1, 'positive'),
(14, 'Several days', 2, 2, 'mild'),
(14, 'More than half the days', 3, 3, 'moderate'),
(14, 'Nearly every day', 4, 4, 'severe'),

(15, 'Not at all', 1, 1, 'positive'),
(15, 'Several days', 2, 2, 'mild'),
(15, 'More than half the days', 3, 3, 'moderate'),
(15, 'Nearly every day', 4, 4, 'severe'),

(16, 'Not at all', 1, 1, 'positive'),
(16, 'Several days', 2, 2, 'mild'),
(16, 'More than half the days', 3, 3, 'moderate'),
(16, 'Nearly every day', 4, 4, 'severe'),

(17, 'Not at all', 1, 1, 'positive'),
(17, 'Several days', 2, 2, 'mild'),
(17, 'More than half the days', 3, 3, 'moderate'),
(17, 'Nearly every day', 4, 4, 'severe'),

(18, 'Not at all', 1, 1, 'positive'),
(18, 'Several days', 2, 2, 'mild'),
(18, 'More than half the days', 3, 3, 'moderate'),
(18, 'Nearly every day', 4, 4, 'severe'),

(19, 'Not at all', 1, 1, 'positive'),
(19, 'Several days', 2, 2, 'mild'),
(19, 'More than half the days', 3, 3, 'moderate'),
(19, 'Nearly every day', 4, 4, 'severe'),

(20, 'Not at all', 1, 1, 'positive'),
(20, 'Several days', 2, 2, 'mild'),
(20, 'More than half the days', 3, 3, 'moderate'),
(20, 'Nearly every day', 4, 4, 'severe');

-- SECTION C: ANXIETY (GAD-7 - 7 questions, 1-4 scale)
-- Intro text: Over the last 2 weeks, how often have you been bothered by any of the following problems?
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(2, 'Feeling nervous, anxious or on edge', 'likert', 18, 'anxiety', TRUE),
(2, 'Not being able to stop or control worrying', 'likert', 19, 'anxiety', TRUE),
(2, 'Worrying too much about different things', 'likert', 20, 'anxiety', TRUE),
(2, 'Trouble relaxing', 'likert', 21, 'anxiety', TRUE),
(2, 'Being so restless that it is hard to sit still', 'likert', 22, 'anxiety', TRUE),
(2, 'Becoming easily annoyed or irritable', 'likert', 23, 'anxiety', TRUE),
(2, 'Feeling afraid as if something awful might happen', 'likert', 24, 'anxiety', TRUE);

-- Anxiety scale options (1-4) for questions 21-27
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(21, 'Not at all', 1, 1, 'positive'),
(21, 'Several days', 2, 2, 'mild'),
(21, 'More than half the days', 3, 3, 'moderate'),
(21, 'Nearly every day', 4, 4, 'severe'),

(22, 'Not at all', 1, 1, 'positive'),
(22, 'Several days', 2, 2, 'mild'),
(22, 'More than half the days', 3, 3, 'moderate'),
(22, 'Nearly every day', 4, 4, 'severe'),

(23, 'Not at all', 1, 1, 'positive'),
(23, 'Several days', 2, 2, 'mild'),
(23, 'More than half the days', 3, 3, 'moderate'),
(23, 'Nearly every day', 4, 4, 'severe'),

(24, 'Not at all', 1, 1, 'positive'),
(24, 'Several days', 2, 2, 'mild'),
(24, 'More than half the days', 3, 3, 'moderate'),
(24, 'Nearly every day', 4, 4, 'severe'),

(25, 'Not at all', 1, 1, 'positive'),
(25, 'Several days', 2, 2, 'mild'),
(25, 'More than half the days', 3, 3, 'moderate'),
(25, 'Nearly every day', 4, 4, 'severe'),

(26, 'Not at all', 1, 1, 'positive'),
(26, 'Several days', 2, 2, 'mild'),
(26, 'More than half the days', 3, 3, 'moderate'),
(26, 'Nearly every day', 4, 4, 'severe'),

(27, 'Not at all', 1, 1, 'positive'),
(27, 'Several days', 2, 2, 'mild'),
(27, 'More than half the days', 3, 3, 'moderate'),
(27, 'Nearly every day', 4, 4, 'severe');

-- Open response for Mental Health Status
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(2, 'We would love to hear from you. How have you been feeling lately—mentally, emotionally, or socially? Feel free to share anything that is helping you feel good or anything that has been challenging.', 'open_text', 25, 'reflection', FALSE);

-- ==============================================================================
-- ASSESSMENT 3: HELP-SEEKING ATTITUDES QUESTIONS
-- ==============================================================================
INSERT INTO questions (assessment_id, question_text, question_type, question_order, category, is_required) VALUES
(3, 'Have you ever received counseling or therapy for mental health concerns?', 'multiple_choice', 1, 'experience', TRUE),
(3, 'Most people think less of a person who has received mental health treatment.', 'likert', 2, 'stigma_perceived', TRUE),
(3, 'I would think less of a person who has received mental health treatment.', 'likert', 3, 'stigma_personal', TRUE),
(3, 'Your experiences and thoughts about mental health support matter. Feel free to share about any counseling you have received or your views on seeking help.', 'open_text', 4, 'reflection', FALSE);

-- Question 29: Counseling experience (multiple choice)
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(29, 'Yes, years ago', 1, 1, 'past_experience'),
(29, 'Yes, recently', 2, 2, 'recent_experience'),
(29, 'No, I have not', 3, 3, 'no_experience');

-- Question 30: Perceived stigma (1-6 scale)
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(30, 'Strongly agree', 1, 1, 'high_stigma'),
(30, 'Agree', 2, 2, 'moderate_stigma'),
(30, 'Somewhat agree', 3, 3, 'mild_stigma'),
(30, 'Somewhat disagree', 4, 4, 'low_stigma'),
(30, 'Disagree', 5, 5, 'positive'),
(30, 'Strongly disagree', 6, 6, 'very_positive');

-- Question 31: Personal stigma (1-6 scale)
INSERT INTO answer_options (question_id, option_text, option_value, option_order, emotional_indicator) VALUES
(31, 'Strongly agree', 1, 1, 'high_stigma'),
(31, 'Agree', 2, 2, 'moderate_stigma'),
(31, 'Somewhat agree', 3, 3, 'mild_stigma'),
(31, 'Somewhat disagree', 4, 4, 'low_stigma'),
(31, 'Disagree', 5, 5, 'positive'),
(31, 'Strongly disagree', 6, 6, 'very_positive');

-- ==============================================================================
-- SEED WELLNESS ACTIVITIES
-- ==============================================================================
INSERT INTO wellness_activities (activity_name, activity_category, description, difficulty_level, estimated_duration, instructions, benefits, is_active) VALUES
('Deep Breathing Exercise', 'breathing', 'A calming breathing technique to reduce stress and anxiety.', 'easy', 5,
 '1. Sit comfortably and close your eyes\n2. Breathe in slowly through your nose for 4 seconds\n3. Hold your breath for 4 seconds\n4. Exhale slowly through your mouth for 6 seconds\n5. Repeat 5-10 times',
 'Reduces stress hormones, lowers heart rate, improves focus', TRUE),

('5-Minute Meditation', 'mindfulness', 'A quick mindfulness meditation for mental clarity.', 'easy', 5,
 '1. Find a quiet spot and sit comfortably\n2. Close your eyes and focus on your breathing\n3. When thoughts arise, acknowledge them and return focus to your breath\n4. Continue for 5 minutes',
 'Improves concentration, reduces anxiety, promotes emotional health', TRUE),

('Gratitude Journaling', 'journaling', 'Write down things you are grateful for to improve mood.', 'easy', 10,
 '1. Get a notebook or open a notes app\n2. Write down 3 things you are grateful for today\n3. For each item, write why you are grateful for it\n4. Try to do this daily',
 'Increases happiness, improves sleep, strengthens relationships', TRUE),

('Progressive Muscle Relaxation', 'exercise', 'Systematically tense and relax muscle groups to release tension.', 'medium', 15,
 '1. Lie down or sit comfortably\n2. Start with your feet - tense the muscles for 5 seconds\n3. Release and relax for 30 seconds\n4. Move up through your body: legs, abdomen, arms, shoulders, face\n5. Notice the difference between tension and relaxation',
 'Reduces physical tension, improves sleep, decreases anxiety', TRUE),

('Nature Walk', 'exercise', 'Take a mindful walk outdoors to connect with nature.', 'easy', 20,
 '1. Go outside to a park or green space\n2. Walk slowly and pay attention to your surroundings\n3. Notice the colors, sounds, and smells around you\n4. Take deep breaths of fresh air\n5. Leave your phone on silent',
 'Reduces stress, improves mood, increases physical activity', TRUE);

-- ==============================================================================
-- SEED THERAPEUTIC GAMES
-- ==============================================================================
INSERT INTO therapeutic_games (game_name, game_type, description, target_emotion, difficulty_level, estimated_duration, instructions, benefits, is_active) VALUES
('Crossroads', 'simulation', 'An endless road-crossing game inspired by classic arcade games. Guide your character safely across busy roads and rivers.', 'stress', 'easy', 10,
 'Use arrow keys or swipe to move your character. Cross roads while avoiding cars and trucks. Time your movements carefully. See how far you can go!',
 'Improves focus and reaction time, provides engaging distraction from stress, promotes mindfulness through concentration', TRUE);

-- ==============================================================================
-- SEED ADMIN USER (Password: Admin123!)
-- ==============================================================================
INSERT INTO users (email, password_hash, first_name, last_name, role_id, is_active) VALUES
('admin@my.nst.edu.ph', '$2y$12$fs6RkkcQInbpJS6RsXUtEOFkROc1zQTnrJM0eteoj3XYAaGaVqdAG', 'System', 'Administrator', 3, TRUE);

-- ==============================================================================
-- SEED SAMPLE COUNSELOR (Password: Counselor123!)
-- ==============================================================================
INSERT INTO users (email, password_hash, first_name, last_name, role_id, is_active) VALUES
('counselor@my.nst.edu.ph', '$2y$12$PfHtZu5.F6LCAscNfWUrk.yP/0ITVIFkzbwZ2AcUp4CBLyu6qn3w.', 'Maria', 'Santos', 2, TRUE);

-- ==============================================================================
-- SUMMARY
-- ==============================================================================
-- Roles: 3 (student, counselor, admin)
-- Sections: 6 (Grade 11-12 SHS)
-- Assessments: 3
--   1. School Experience (3 questions: 2 likert + 1 open)
--   2. Mental Health Status (25 questions: 8 positive MH + 9 depression + 7 anxiety + 1 open)
--   3. Help-Seeking Attitudes (4 questions: 1 multiple choice + 2 likert + 1 open)
-- Total Questions: 32
-- Wellness Activities: 5
-- Therapeutic Games: 1 (Crossroads - endless road crossing game)
-- Users: 2 (admin and sample counselor)
-- 
-- Test Accounts:
-- Admin: admin@my.nst.edu.ph / Admin123!
-- Counselor: counselor@my.nst.edu.ph / Counselor123!
-- ==============================================================================
