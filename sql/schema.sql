CREATE DATABASE IF NOT EXISTS bhp_platform DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bhp_platform;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    passport_or_pesel VARCHAR(64) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    language VARCHAR(5) NOT NULL DEFAULT 'pl',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    language VARCHAR(5) NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS course_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_material_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    question_text TEXT NOT NULL,
    language VARCHAR(5) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    CONSTRAINT fk_answer_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    score_percent INT NOT NULL,
    passed TINYINT(1) NOT NULL DEFAULT 0,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_result_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_result_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    certificate_number VARCHAR(50) NOT NULL,
    pdf_path VARCHAR(255) NOT NULL,
    signed_scan_path VARCHAR(255),
    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificate_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_certificate_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample data
INSERT INTO admins (email, password_hash) VALUES
('admin@agatgroup.pl', '$2y$12$C26FRtP5Lgovzg74Xo7BzuOf9GGvLusOnZRTfn9/VxtXG4.9Pd.7y');

INSERT INTO users (first_name, last_name, passport_or_pesel, email, password_hash, language) VALUES
('Anna', 'Kowalska', 'AB1234567', 'anna@example.com', '$2y$12$nKatfOCzynv1Ti9RR0JOyuEBH8ymZ563mYg1Q8JM2yMIU843FezeC', 'pl'),
('Nguyen', 'Thi Hoa', 'VN998877', 'hoa@example.com', '$2y$12$nKatfOCzynv1Ti9RR0JOyuEBH8ymZ563mYg1Q8JM2yMIU843FezeC', 'vi'),
('Ivan', 'Petrov', 'RU665544', 'ivan@example.com', '$2y$12$nKatfOCzynv1Ti9RR0JOyuEBH8ymZ563mYg1Q8JM2yMIU843FezeC', 'ru');

INSERT INTO courses (title, description, language, file_path) VALUES
('Szkolenie BHP - Magazyn', 'Podstawowe zasady bezpieczeństwa pracy w magazynie.', 'pl', 'uploads/bhp_magazyn.pdf');

INSERT INTO course_materials (course_id, title, file_path) VALUES
(1, 'Prezentacja BHP', 'uploads/bhp_presentation.pdf'),
(1, 'Instrukcja PDF', 'uploads/bhp_manual.pdf'),
(1, 'Film szkoleniowy', 'uploads/bhp_video.mp4');

INSERT INTO questions (course_id, question_text, language) VALUES
(1, 'Jakie środki ochrony indywidualnej należy nosić w strefie załadunku?', 'pl'),
(1, 'Jak należy reagować w przypadku rozlania substancji chemicznej?', 'pl'),
(1, 'Co należy zrobić przed rozpoczęciem pracy z wózkiem widłowym?', 'pl'),
(1, 'Jakie czynności wykonujemy po zakończeniu zmiany?', 'pl'),
(1, 'Kto odpowiada za zgłoszenie zagrożenia w magazynie?', 'pl');

INSERT INTO answers (question_id, answer_text, is_correct) VALUES
(1, 'Kask, kamizelkę odblaskową i buty ochronne', 1),
(1, 'Tylko rękawice ochronne', 0),
(1, 'Okulary przeciwsłoneczne', 0),
(1, 'Żadnych', 0),
(2, 'Zabezpieczyć miejsce, poinformować przełożonego i użyć zestawu neutralizującego', 1),
(2, 'Ignorować, substancja sama odparuje', 0),
(2, 'Zostawić miejsce i wrócić po przerwie', 0),
(2, 'Posprzątać wodą bez zgłaszania', 0),
(3, 'Sprawdzić stan techniczny i zrobić test funkcjonalny', 1),
(3, 'Zaufać poprzedniej zmianie', 0),
(3, 'Od razu rozpocząć pracę', 0),
(3, 'Poczekać na sygnał dźwiękowy', 0),
(4, 'Posprzątać stanowisko i zgłosić problemy przełożonemu', 1),
(4, 'Szybko wyjść bez sprawdzania', 0),
(4, 'Zostawić narzędzia gdziekolwiek', 0),
(4, 'Wyłączyć światła w całym magazynie', 0),
(5, 'Każdy pracownik natychmiast po zauważeniu zagrożenia', 1),
(5, 'Tylko kierownik zmiany', 0),
(5, 'Wyłącznie dział BHP raz w tygodniu', 0),
(5, 'Nikt, jeśli sytuacja wydaje się drobna', 0);

INSERT INTO results (user_id, course_id, score_percent, passed) VALUES
(1, 1, 92, 1);

INSERT INTO certificates (user_id, course_id, certificate_number, pdf_path, signed_scan_path) VALUES
(1, 1, 'BHP-2025-000001', 'certificates/certificate_sample.pdf', NULL);
