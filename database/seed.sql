-- Demo data. Run after schema.sql.
-- Demo accounts (password for both: Password123)
--   admin@ump.test    — administrator
--   student@ump.test  — regular user
-- Event dates are relative to the moment you import, so the demo never goes stale.

USE `ump_event_booking`;

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`) VALUES
(1, 'Lindiwe Dlamini', 'admin@ump.test', '$2y$10$E9p/G95VaEcLThLbRCcz4uPF1xM2u5lfMNqOZ9Cazoit0RXWUggR.', 'admin'),
(2, 'Thandi Mokoena', 'student@ump.test', '$2y$10$E9p/G95VaEcLThLbRCcz4uPF1xM2u5lfMNqOZ9Cazoit0RXWUggR.', 'user');

INSERT INTO `events` (`id`, `title`, `slug`, `description`, `category`, `venue`, `starts_at`, `ends_at`, `capacity`, `image_path`, `status`, `created_by`) VALUES
(1, 'Career Expo 2026', 'career-expo-2026',
 'Meet recruiters from more than 40 companies across tech, finance, agriculture and the public sector. Bring printed CVs, join the CV-clinic sessions and attend short talks on landing your first graduate role.',
 'career', 'Multipurpose Hall, Mbombela Campus',
 TIMESTAMP(DATE(NOW() + INTERVAL 6 DAY), '09:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 6 DAY), '15:00:00'),
 250, 'assets/img/events/career-expo.jpg', 'published', 1),

(2, 'UMP Hackathon: Build for Mpumalanga', 'ump-hackathon-build-for-mpumalanga',
 'A 24-hour hackathon where teams of up to four build solutions for local challenges in agriculture, tourism and public services. Mentors, meals and Wi-Fi provided. Beginners welcome.',
 'tech', 'ICT Labs, Building 6',
 TIMESTAMP(DATE(NOW() + INTERVAL 12 DAY), '10:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 13 DAY), '10:00:00'),
 60, 'assets/img/events/hackathon.jpg', 'published', 1),

(3, 'Spring Music Festival', 'spring-music-festival',
 'An open-air evening of live music featuring student bands, the UMP Jazz Ensemble and a headline act. Food trucks open from 16:00.',
 'music', 'Main Lawn, Mbombela Campus',
 TIMESTAMP(DATE(NOW() + INTERVAL 18 DAY), '16:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 18 DAY), '22:00:00'),
 500, 'assets/img/events/music-festival.jpg', 'published', 1),

(4, 'Inter-Faculty Sports Day', 'inter-faculty-sports-day',
 'Faculties go head-to-head in athletics, soccer, netball and tug-of-war. Come compete or come cheer — every faculty needs its supporters.',
 'sports', 'UMP Sports Grounds',
 TIMESTAMP(DATE(NOW() + INTERVAL 25 DAY), '08:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 25 DAY), '17:00:00'),
 300, 'assets/img/events/sports.jpg', 'published', 1),

(5, 'UMP Choir: Voices of the Lowveld', 'ump-choir-voices-of-the-lowveld',
 'The award-winning UMP Choir performs a programme of South African choral classics and new arrangements. Seating is limited.',
 'culture', 'Great Hall',
 TIMESTAMP(DATE(NOW() + INTERVAL 9 DAY), '18:30:00'), TIMESTAMP(DATE(NOW() + INTERVAL 9 DAY), '20:30:00'),
 3, 'assets/img/events/choir.jpg', 'published', 1),

(6, 'Heritage Day Celebration', 'heritage-day-celebration',
 'Celebrate the cultures of our campus community with traditional dress, dance, food stalls and storytelling.',
 'culture', 'Student Centre Courtyard',
 TIMESTAMP(DATE(NOW() + INTERVAL 32 DAY), '11:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 32 DAY), '16:00:00'),
 400, 'assets/img/events/heritage.jpg', 'published', 1),

(7, 'Research & Innovation Showcase', 'research-innovation-showcase',
 'Postgraduate students present posters and live demos of their research. Network with faculty and industry partners.',
 'academic', 'Science Building Foyer',
 TIMESTAMP(DATE(NOW() + INTERVAL 40 DAY), '10:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 40 DAY), '14:00:00'),
 150, 'assets/img/events/science.jpg', 'published', 1),

(8, 'Graduation Ball', 'graduation-ball',
 'Celebrate the class of the year with dinner, dancing and awards.',
 'social', 'Great Hall',
 TIMESTAMP(DATE(NOW() + INTERVAL 55 DAY), '19:00:00'), TIMESTAMP(DATE(NOW() + INTERVAL 55 DAY), '23:59:00'),
 200, 'assets/img/events/graduation.jpg', 'draft', 1),

(9, 'Welcome Week Social', 'welcome-week-social',
 'Meet new friends, societies and clubs at the start of the semester.',
 'social', 'Student Centre',
 TIMESTAMP(DATE(NOW() - INTERVAL 20 DAY), '12:00:00'), TIMESTAMP(DATE(NOW() - INTERVAL 20 DAY), '16:00:00'),
 200, NULL, 'published', 1);

INSERT INTO `bookings` (`reference`, `user_id`, `event_id`, `quantity`, `status`, `created_at`) VALUES
('UMP-7K4Q9WXR', 2, 1, 1, 'confirmed', NOW() - INTERVAL 2 DAY),
('UMP-H3M8ZP2D', 2, 5, 2, 'confirmed', NOW() - INTERVAL 1 DAY),
('UMP-R6T2CB8N', 2, 9, 1, 'confirmed', NOW() - INTERVAL 25 DAY),
('UMP-Q9V4LJ7S', 1, 5, 1, 'confirmed', NOW() - INTERVAL 1 DAY);
