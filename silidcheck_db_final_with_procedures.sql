-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 02:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `silidcheck_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddQuestion` (IN `p_quiz_id` INT, IN `p_question_text` TEXT, IN `p_question_type` VARCHAR(50), IN `p_option_a` TEXT, IN `p_option_b` TEXT, IN `p_option_c` TEXT, IN `p_option_d` TEXT, IN `p_correct_answer` VARCHAR(1))   BEGIN
    INSERT INTO quiz_questions (
        quiz_id,
        question_text,
        question_type,
        option_a,
        option_b,
        option_c,
        option_d,
        correct_answer
    ) VALUES (
        p_quiz_id,
        p_question_text,
        p_question_type,
        p_option_a,
        p_option_b,
        p_option_c,
        p_option_d,
        p_correct_answer
    );
    
    SELECT LAST_INSERT_ID() AS question_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddQuizQuestion` (IN `quiz_id_param` INT, IN `question_param` TEXT, IN `answer_param` TEXT)   BEGIN
    INSERT INTO quiz_questions (quiz_id, question, correct_answer)
    VALUES (quiz_id_param, question_param, answer_param);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddResult` (IN `p_quiz_id` INT, IN `p_student_id` INT, IN `p_score` INT, IN `p_max_score` INT, IN `p_time_taken` INT)   BEGIN
    -- Check if a result already exists
    DECLARE existing_id INT;
    
    SELECT id INTO existing_id
    FROM quiz_results
    WHERE quiz_id = p_quiz_id AND student_id = p_student_id
    LIMIT 1;
    
    IF existing_id IS NULL THEN
        -- Insert new result
        INSERT INTO quiz_results (
            quiz_id,
            student_id,
            score,
            max_score,
            time_taken,
            taken_at
        ) VALUES (
            p_quiz_id,
            p_student_id,
            p_score,
            p_max_score,
            p_time_taken,
            NOW()
        );
        
        SELECT LAST_INSERT_ID() AS result_id;
    ELSE
        -- Update existing result
        UPDATE quiz_results
        SET 
            score = p_score,
            max_score = p_max_score,
            time_taken = p_time_taken,
            taken_at = NOW()
        WHERE id = existing_id;
        
        SELECT existing_id AS result_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddSubject` (IN `p_subject_name` VARCHAR(100), IN `p_description` TEXT, IN `p_teacher_id` INT)   BEGIN
    INSERT INTO subjects (subject_name, description, teacher_id, created_at)
    VALUES (p_subject_name, p_description, p_teacher_id, NOW());
    
    SELECT LAST_INSERT_ID() AS subject_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddTask` (IN `p_title` VARCHAR(255), IN `p_subject` VARCHAR(100), IN `p_description` TEXT, IN `p_teacher_id` INT, IN `p_time_limit` INT, IN `p_status` VARCHAR(20))   BEGIN
    INSERT INTO quizzes (
        title, 
        subject, 
        description, 
        teacher_id, 
        time_limit,
        status,
        created_at
    ) VALUES (
        p_title,
        p_subject,
        p_description,
        p_teacher_id,
        p_time_limit,
        p_status,
        NOW()
    );
    
    SELECT LAST_INSERT_ID() AS quiz_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AuthenticateStudent` (IN `p_email` VARCHAR(255))   BEGIN
    SELECT id, name, password
    FROM students
    WHERE email = p_email;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AuthenticateTeacher` (IN `p_email` VARCHAR(255))   BEGIN
    SELECT id, name, password
    FROM teachers
    WHERE email = p_email;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckEmailExists` (IN `p_email` VARCHAR(255), IN `p_user_type` VARCHAR(20))   BEGIN
    DECLARE email_count INT DEFAULT 0;
    
    IF p_user_type = 'student' THEN
        SELECT COUNT(*) INTO email_count
        FROM students 
        WHERE email = p_email;
    ELSEIF p_user_type = 'teacher' THEN
        SELECT COUNT(*) INTO email_count
        FROM teachers
        WHERE email = p_email;
    END IF;
    
    SELECT email_count AS exists_count;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateQuestion` (IN `p_quiz_id` INT, IN `p_question_text` TEXT, IN `p_question_type` VARCHAR(50), IN `p_options` JSON, IN `p_correct_answer` VARCHAR(255))   BEGIN
    DECLARE option_a TEXT;
    DECLARE option_b TEXT;
    DECLARE option_c TEXT;
    DECLARE option_d TEXT;
    
    -- Extract options from JSON
    SET option_a = JSON_UNQUOTE(JSON_EXTRACT(p_options, '$.A'));
    SET option_b = JSON_UNQUOTE(JSON_EXTRACT(p_options, '$.B'));
    SET option_c = JSON_UNQUOTE(JSON_EXTRACT(p_options, '$.C'));
    SET option_d = JSON_UNQUOTE(JSON_EXTRACT(p_options, '$.D'));
    
    INSERT INTO quiz_questions (
        quiz_id,
        question_text,
        question_type,
        option_a,
        option_b,
        option_c,
        option_d,
        correct_answer
    ) VALUES (
        p_quiz_id,
        p_question_text,
        p_question_type,
        option_a,
        option_b,
        option_c,
        option_d,
        p_correct_answer
    );
    
    SELECT LAST_INSERT_ID() AS question_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateQuiz` (IN `teacher_id_param` INT, IN `title_param` VARCHAR(255), IN `subject_param` VARCHAR(100))   BEGIN
    INSERT INTO quizzes (teacher_id, title, subject, created_at)
    VALUES (teacher_id_param, title_param, subject_param, NOW());
    
    SELECT LAST_INSERT_ID() AS quiz_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateQuizResultsTable` ()   BEGIN
    DECLARE tableExists INT DEFAULT 0;
    
    -- Check if the table already exists
    SELECT COUNT(*) INTO tableExists 
    FROM information_schema.tables 
    WHERE table_schema = DATABASE() 
    AND table_name = 'quiz_results';
    
    -- Create the table only if it doesn't exist
    IF tableExists = 0 THEN
        CREATE TABLE quiz_results (
            id INT AUTO_INCREMENT PRIMARY KEY,
            quiz_id INT NOT NULL,
            student_id INT NOT NULL,
            score INT NOT NULL DEFAULT 0,
            max_score INT NOT NULL DEFAULT 0,
            answers JSON,
            feedback TEXT,
            time_taken INT DEFAULT 0,
            taken_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_quiz_id (quiz_id),
            INDEX idx_student_id (student_id),
            CONSTRAINT fk_quiz_results_quiz FOREIGN KEY (quiz_id) 
                REFERENCES quizzes(quiz_id) ON DELETE CASCADE,
            CONSTRAINT fk_quiz_results_student FOREIGN KEY (student_id) 
                REFERENCES students(student_id) ON DELETE CASCADE
        );
        
        SELECT 'quiz_results table created successfully' AS message;
    ELSE
        SELECT 'quiz_results table already exists' AS message;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateResult` (IN `p_quiz_id` INT)   BEGIN
    -- Create an empty result template
    INSERT INTO quiz_result_templates (
        quiz_id,
        passing_score,
        max_score,
        created_at
    ) VALUES (
        p_quiz_id,
        0,  -- Default passing score
        0,  -- Default max score
        NOW()
    );
    
    SELECT LAST_INSERT_ID() AS template_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateTitle` (IN `p_quiz_id` INT, IN `p_title` VARCHAR(255))   BEGIN
    UPDATE quizzes
    SET title = p_title
    WHERE id = p_quiz_id;
    
    SELECT ROW_COUNT() AS rows_updated;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteQuestion` (IN `p_question_id` INT)   BEGIN
    DELETE FROM quiz_questions
    WHERE id = p_question_id;
    
    SELECT ROW_COUNT() AS rows_deleted;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteTask` (IN `p_quiz_id` INT)   BEGIN
    START TRANSACTION;
    
    -- Delete all questions related to the quiz
    DELETE FROM quiz_questions
    WHERE quiz_id = p_quiz_id;
    
    -- Delete all results related to the quiz
    DELETE FROM quiz_results
    WHERE quiz_id = p_quiz_id;
    
    -- Delete any result templates
    DELETE FROM quiz_result_templates
    WHERE quiz_id = p_quiz_id;
    
    -- Delete the quiz itself
    DELETE FROM quizzes
    WHERE id = p_quiz_id;
    
    COMMIT;
    
    SELECT ROW_COUNT() AS rows_deleted;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteTitle` (IN `p_quiz_id` INT, IN `p_teacher_id` INT)   BEGIN
    -- First verify the teacher owns this quiz
    DECLARE quiz_owner INT;
    
    SELECT teacher_id INTO quiz_owner 
    FROM quizzes 
    WHERE quiz_id = p_quiz_id;
    
    IF quiz_owner = p_teacher_id THEN
        -- Delete the quiz and all related questions
        START TRANSACTION;
        
        -- Delete results first (foreign key constraint)
        DELETE FROM results WHERE quiz_id = p_quiz_id;
        
        -- Delete questions
        DELETE FROM questions WHERE quiz_id = p_quiz_id;
        
        -- Delete the quiz itself
        DELETE FROM quizzes WHERE quiz_id = p_quiz_id;
        
        COMMIT;
        
        SELECT TRUE AS success, 'Quiz deleted successfully' AS message;
    ELSE
        SELECT FALSE AS success, 'Unauthorized: You cannot delete this quiz' AS message;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterScoresByDate` (IN `teacher_id_param` INT, IN `date_filter_param` VARCHAR(20))   BEGIN
    DECLARE date_limit DATETIME;
    
    -- Set the date limit based on filter
    CASE date_filter_param
        WHEN 'week' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 WEEK);
        WHEN 'month' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 MONTH);
        WHEN 'quarter' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 3 MONTH);
        ELSE SET date_limit = '1900-01-01';
    END CASE;
    
    SELECT 
        s.id AS student_id,
        s.student_id AS student_number,
        s.name AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
        AND qr.taken_at >= date_limit
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterScoresByQuiz` (IN `teacher_id_param` INT, IN `quiz_id_param` INT)   BEGIN
    SELECT 
        s.id AS student_id,
        s.student_id AS student_number,
        s.name AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
        AND q.id = quiz_id_param
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterScoresByQuizAndDate` (IN `teacher_id_param` INT, IN `quiz_id_param` INT, IN `date_filter_param` VARCHAR(20))   BEGIN
    DECLARE date_limit DATETIME;
    
    -- Set the date limit based on filter
    CASE date_filter_param
        WHEN 'week' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 WEEK);
        WHEN 'month' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 MONTH);
        WHEN 'quarter' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 3 MONTH);
        ELSE SET date_limit = '1900-01-01';
    END CASE;
    
    SELECT 
        s.first_name,
        s.last_name,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
        AND q.id = quiz_id_param
        AND qr.taken_at >= date_limit
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterScoresByQuizAndStudent` (IN `teacher_id_param` INT, IN `quiz_id_param` INT, IN `student_name_param` VARCHAR(100))   BEGIN
    SELECT 
        s.first_name,
        s.last_name,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
        AND q.id = quiz_id_param
        AND (
            CONCAT(s.first_name, ' ', s.last_name) LIKE CONCAT('%', student_name_param, '%')
            OR s.student_id LIKE CONCAT('%', student_name_param, '%')
        )
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterScoresByStudent` (IN `teacher_id_param` INT, IN `student_name_param` VARCHAR(100))   BEGIN
    SELECT 
        s.id AS student_id,
        s.student_id AS student_number,
        s.name AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
        AND (
            s.name LIKE CONCAT('%', student_name_param, '%')
            OR s.student_id LIKE CONCAT('%', student_name_param, '%')
        )
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterStudentScoresByDate` (IN `student_id` INT, IN `date_range` VARCHAR(20))   BEGIN
    DECLARE date_limit DATETIME;
    
    -- Set date limit based on selected range
    CASE date_range
        WHEN 'week' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 WEEK);
        WHEN 'month' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 MONTH);
        WHEN 'quarter' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 3 MONTH);
        ELSE SET date_limit = DATE_SUB(NOW(), INTERVAL 10 YEAR); -- Default to all
    END CASE;
    
    SELECT 
        qr.score,
        q.title,
        q.subject,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
        AND qr.taken_at >= date_limit
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterStudentScoresBySubject` (IN `student_id` INT, IN `subject_name` VARCHAR(100))   BEGIN
    SELECT 
        qr.score,
        q.title,
        q.subject,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
        AND q.subject = subject_name
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterStudentScoresBySubjectAndDate` (IN `student_id` INT, IN `subject_name` VARCHAR(100), IN `date_range` VARCHAR(20))   BEGIN
    DECLARE date_limit DATETIME;
    
    -- Set date limit based on selected range
    CASE date_range
        WHEN 'week' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 WEEK);
        WHEN 'month' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 1 MONTH);
        WHEN 'quarter' THEN SET date_limit = DATE_SUB(NOW(), INTERVAL 3 MONTH);
        ELSE SET date_limit = DATE_SUB(NOW(), INTERVAL 10 YEAR); -- Default to all
    END CASE;
    
    SELECT 
        qr.score,
        q.title,
        q.subject,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
        AND q.subject = subject_name
        AND qr.taken_at >= date_limit
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FindStudentByNameEmailAndQuiz` (IN `p_search` VARCHAR(255), IN `p_teacher_id` INT)   BEGIN
    SELECT 
        s.student_id,
        s.first_name,
        s.last_name,
        s.email,
        s.profile_image,
        GROUP_CONCAT(DISTINCT sub.subject_name SEPARATOR ', ') AS enrolled_subjects,
        COUNT(DISTINCT r.result_id) AS completed_quizzes,
        AVG(r.score / q.total_points) * 100 AS average_score
    FROM 
        students s
    LEFT JOIN 
        enrollments e ON s.student_id = e.student_id
    LEFT JOIN 
        subjects sub ON e.subject_id = sub.subject_id
    LEFT JOIN 
        results r ON s.student_id = r.student_id
    LEFT JOIN 
        quizzes q ON r.quiz_id = q.quiz_id
    WHERE 
        (s.first_name LIKE CONCAT('%', p_search, '%')
        OR s.last_name LIKE CONCAT('%', p_search, '%')
        OR s.email LIKE CONCAT('%', p_search, '%')
        OR CONCAT(s.first_name, ' ', s.last_name) LIKE CONCAT('%', p_search, '%')
        OR EXISTS (
            SELECT 1 
            FROM results r2 
            JOIN quizzes q2 ON r2.quiz_id = q2.quiz_id 
            WHERE r2.student_id = s.student_id 
            AND q2.title LIKE CONCAT('%', p_search, '%')
        ))
        AND (p_teacher_id IS NULL OR sub.teacher_id = p_teacher_id)
    GROUP BY 
        s.student_id
    ORDER BY 
        s.last_name, s.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `FindTeacherByNameEmailAndQuiz` (IN `p_search` VARCHAR(255))   BEGIN
    SELECT 
        t.teacher_id,
        t.first_name,
        t.last_name,
        t.email,
        t.profile_image,
        t.department,
        GROUP_CONCAT(DISTINCT s.subject_name SEPARATOR ', ') AS subjects,
        COUNT(DISTINCT q.quiz_id) AS quiz_count
    FROM 
        teachers t
    LEFT JOIN 
        subjects s ON t.teacher_id = s.teacher_id
    LEFT JOIN 
        quizzes q ON s.subject_id = q.subject_id
    WHERE 
        t.first_name LIKE CONCAT('%', p_search, '%')
        OR t.last_name LIKE CONCAT('%', p_search, '%')
        OR t.email LIKE CONCAT('%', p_search, '%')
        OR CONCAT(t.first_name, ' ', t.last_name) LIKE CONCAT('%', p_search, '%')
        OR EXISTS (
            SELECT 1 
            FROM quizzes q2 
            WHERE q2.teacher_id = t.teacher_id 
            AND q2.title LIKE CONCAT('%', p_search, '%')
        )
    GROUP BY 
        t.teacher_id
    ORDER BY 
        t.last_name, t.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllStudents` ()   BEGIN
    SELECT 
        s.id,
        s.student_id,
        s.first_name,
        s.last_name,
        s.email,
        s.year_level,
        s.is_active,
        COUNT(qr.id) AS quizzes_taken,
        IFNULL(AVG((qr.score / (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id)) * 100), 0) AS avg_score
    FROM 
        students s
    LEFT JOIN 
        quiz_results qr ON s.id = qr.student_id
    LEFT JOIN 
        quizzes q ON qr.quiz_id = q.id
    GROUP BY 
        s.id
    ORDER BY 
        s.last_name, s.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllStudentsForTeacher` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        s.id,
        s.name, 
        s.email,
        s.grade,
        COUNT(DISTINCT qr.id) AS quizzes_taken,
        IFNULL(AVG((qr.score / qr.total) * 100), 0) AS avg_score
    FROM 
        students s
    LEFT JOIN 
        quiz_results qr ON s.id = qr.student_id
    LEFT JOIN 
        quizzes q ON qr.quiz_id = q.id AND q.teacher_id = teacher_id_param
    GROUP BY 
        s.id, s.name, s.email, s.grade
    ORDER BY 
        s.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPerformance` (IN `p_student_id` INT, IN `p_subject_id` INT)   BEGIN
    SELECT 
        q.quiz_id,
        q.title,
        q.total_points,
        r.score,
        (r.score / q.total_points) * 100 AS percentage,
        r.submission_date
    FROM 
        results r
    JOIN 
        quizzes q ON r.quiz_id = q.quiz_id
    WHERE 
        r.student_id = p_student_id
        AND q.subject_id = IFNULL(p_subject_id, q.subject_id)
    ORDER BY 
        r.submission_date DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetQuestions` (IN `p_quiz_id` INT)   BEGIN
    SELECT 
        question_id,
        quiz_id,
        question_text,
        question_type,
        points,
        options
    FROM 
        questions
    WHERE 
        quiz_id = p_quiz_id
    ORDER BY 
        question_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetQuizQuestions` (IN `p_quiz_id` INT)   BEGIN
    SELECT 
        id,
        question_text,
        question_type,
        option_a,
        option_b,
        option_c,
        option_d,
        correct_answer
    FROM 
        quiz_questions
    WHERE 
        quiz_id = p_quiz_id
    ORDER BY
        id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetQuizTakenCount` (IN `p_quiz_id` INT)   BEGIN
    -- Get count of distinct students who have taken this quiz
    SELECT 
        COUNT(DISTINCT r.student_id) as taken_count,
        q.title as quiz_title,
        q.total_points as max_possible_score,
        AVG(r.score) as average_score,
        MIN(r.score) as lowest_score,
        MAX(r.score) as highest_score,
        AVG(r.time_taken) as average_time_seconds
    FROM 
        results r
    JOIN
        quizzes q ON r.quiz_id = q.quiz_id
    WHERE 
        r.quiz_id = p_quiz_id
    GROUP BY
        r.quiz_id, q.title, q.total_points;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetQuizzes` (IN `p_teacher_id` INT, IN `p_subject_id` INT, IN `p_student_id` INT)   BEGIN
    IF p_student_id IS NOT NULL THEN
        -- Get quizzes available to a specific student
        SELECT 
            q.quiz_id,
            q.title,
            q.description,
            q.subject_id,
            s.subject_name,
            q.teacher_id,
            CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
            q.time_limit,
            q.total_points,
            q.due_date,
            q.created_at,
            r.result_id IS NOT NULL AS is_completed,
            r.score
        FROM 
            quizzes q
        JOIN 
            subjects s ON q.subject_id = s.subject_id
        JOIN 
            teachers t ON q.teacher_id = t.teacher_id
        JOIN 
            enrollments e ON s.subject_id = e.subject_id
        LEFT JOIN 
            results r ON q.quiz_id = r.quiz_id AND r.student_id = p_student_id
        WHERE 
            e.student_id = p_student_id
            AND (s.subject_id = p_subject_id OR p_subject_id IS NULL)
        ORDER BY 
            q.due_date, q.title;
    ELSE
        -- Get quizzes created by a specific teacher
        SELECT 
            q.quiz_id,
            q.title,
            q.description,
            q.subject_id,
            s.subject_name,
            q.time_limit,
            q.total_points,
            q.due_date,
            q.created_at,
            COUNT(DISTINCT r.student_id) AS submissions_count,
            AVG(r.score) AS average_score
        FROM 
            quizzes q
        JOIN 
            subjects s ON q.subject_id = s.subject_id
        LEFT JOIN 
            results r ON q.quiz_id = r.quiz_id
        WHERE 
            q.teacher_id = IFNULL(p_teacher_id, q.teacher_id)
            AND (q.subject_id = p_subject_id OR p_subject_id IS NULL)
        GROUP BY 
            q.quiz_id
        ORDER BY 
            q.created_at DESC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecentQuizScores` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        s.id AS student_id,
        s.name AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    JOIN 
        students s ON qr.student_id = s.id
    WHERE 
        q.teacher_id = teacher_id_param
    ORDER BY 
        qr.taken_at DESC
    LIMIT 50;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetResult` (IN `p_student_id` INT, IN `p_quiz_id` INT)   BEGIN
    SELECT 
        r.result_id,
        r.quiz_id,
        q.title AS quiz_title,
        r.student_id,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        r.score,
        q.total_points,
        (r.score / q.total_points) * 100 AS percentage,
        r.answers,
        r.feedback,
        r.submission_date,
        r.time_taken
    FROM 
        results r
    JOIN 
        quizzes q ON r.quiz_id = q.quiz_id
    JOIN 
        students s ON r.student_id = s.student_id
    WHERE 
        r.student_id = IFNULL(p_student_id, r.student_id)
        AND r.quiz_id = IFNULL(p_quiz_id, r.quiz_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetScoreStatistics` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        AVG((qr.score / (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id)) * 100) AS avg_score,
        MAX((qr.score / (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id)) * 100) AS max_score,
        COUNT(DISTINCT qr.student_id) AS total_students,
        COUNT(*) AS total_attempts
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        q.teacher_id = teacher_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentById` (IN `p_student_id` INT)   BEGIN
    SELECT 
        student_id,
        first_name,
        last_name,
        email,
        profile_image
    FROM 
        students
    WHERE 
        student_id = p_student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentPerformanceChartData` (IN `student_id` INT)   BEGIN
    SELECT 
        q.title, 
        qr.score,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_questions,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
    ORDER BY 
        qr.taken_at DESC
    LIMIT 5;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentPerformanceStats` (IN `student_id` INT)   BEGIN
    SELECT 
        -- Calculate average score as percentage based on quiz questions count
        AVG((qr.score / (
            SELECT COUNT(*) 
            FROM quiz_questions qq 
            WHERE qq.quiz_id = q.id
        )) * 100) as average_score,
        
        -- Calculate highest score as percentage
        MAX((qr.score / (
            SELECT COUNT(*) 
            FROM quiz_questions qq 
            WHERE qq.quiz_id = q.id
        )) * 100) as highest_score,
        
        -- Count of total quizzes taken
        COUNT(*) as quizzes_taken
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentProfileById` (IN `student_id_param` INT)   BEGIN
    SELECT 
        id,
        name,
        email,
        grade
    FROM 
        students
    WHERE 
        id = student_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentQuizScores` (IN `student_id` INT)   BEGIN
    SELECT 
        qr.score,
        q.title,
        q.subject,
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) as total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentQuizzes` (IN `p_student_id` INT)   BEGIN
    SELECT 
        q.id,
        q.title,
        q.subject,
        q.description,
        q.time_limit,
        q.status,
        IFNULL(qr.score, 0) AS score,
        IFNULL(qr.max_score, 0) AS max_score,
        IFNULL(qr.taken_at, NULL) AS taken_at,
        t.name AS teacher_name
    FROM 
        quizzes q
    LEFT JOIN 
        quiz_results qr ON q.id = qr.quiz_id AND qr.student_id = p_student_id
    LEFT JOIN
        teachers t ON q.teacher_id = t.id
    WHERE 
        q.status = 'published'
    ORDER BY
        qr.taken_at DESC,
        q.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentRecentScores` (IN `student_id_param` INT)   BEGIN
    SELECT 
        q.title AS quiz_title,
        q.subject,
        qr.score,
        qr.total AS total_items,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id_param
    ORDER BY 
        qr.taken_at DESC
    LIMIT 20;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudents` (IN `p_teacher_id` INT, IN `p_subject_id` INT)   BEGIN
    IF p_subject_id IS NOT NULL THEN
        -- Get students enrolled in a specific subject
        SELECT 
            s.student_id,
            s.first_name,
            s.last_name,
            s.email,
            s.profile_image,
            es.enrollment_date
        FROM 
            students s
        JOIN 
            enrollments es ON s.student_id = es.student_id
        WHERE 
            es.subject_id = p_subject_id
        ORDER BY 
            s.last_name, s.first_name;
    ELSE
        -- Get all students for a specific teacher
        SELECT DISTINCT
            s.student_id,
            s.first_name,
            s.last_name,
            s.email,
            s.profile_image
        FROM 
            students s
        JOIN 
            enrollments e ON s.student_id = e.student_id
        JOIN 
            subjects subj ON e.subject_id = subj.subject_id
        WHERE 
            subj.teacher_id = p_teacher_id
        ORDER BY 
            s.last_name, s.first_name;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentScoreSummary` (IN `student_id` INT)   BEGIN
    SELECT 
        COUNT(*) as total_quizzes,
        AVG((qr.score / (
            SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id
        )) * 100) as avg_score,
        MAX((qr.score / (
            SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id
        )) * 100) as highest_score
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentStatistics` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        COUNT(DISTINCT s.id) AS total_students,
        COUNT(DISTINCT s.id) AS active_students,
        IFNULL(AVG(student_counts.quiz_count), 0) AS avg_participation
    FROM 
        students s
    INNER JOIN 
        quiz_results qr ON s.id = qr.student_id
    INNER JOIN 
        quizzes q ON qr.quiz_id = q.id
    INNER JOIN (
        SELECT 
            student_id, 
            COUNT(*) AS quiz_count
        FROM 
            quiz_results qr
        INNER JOIN 
            quizzes q ON qr.quiz_id = q.id
        WHERE 
            q.teacher_id = teacher_id_param
        GROUP BY 
            student_id
    ) student_counts ON s.id = student_counts.student_id
    WHERE 
        q.teacher_id = teacher_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentStats` (IN `student_id_param` INT)   BEGIN
    SELECT 
        IFNULL(AVG((qr.score / qr.total) * 100), 0) AS avg_score,
        IFNULL(MAX((qr.score / qr.total) * 100), 0) AS highest_score,
        COUNT(qr.id) AS quizzes_taken,
        MAX(qr.taken_at) AS last_active
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentSubjectPerformance` (IN `student_id` INT)   BEGIN
    SELECT 
        q.subject, 
        -- Calculate average score by subject
        AVG((qr.score / (
            SELECT COUNT(*) 
            FROM quiz_questions qq 
            WHERE qq.quiz_id = q.id
        )) * 100) as avg_score,
        
        -- Count of quizzes taken per subject
        COUNT(*) as quiz_count
    FROM 
        quiz_results qr
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        qr.student_id = student_id
    GROUP BY 
        q.subject
    ORDER BY 
        avg_score DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStudentSubjects` (IN `student_id` INT)   BEGIN
    SELECT DISTINCT q.subject
    FROM quizzes q
    JOIN quiz_results qr ON qr.quiz_id = q.id
    WHERE qr.student_id = student_id
    ORDER BY q.subject;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTeacherById` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        id, 
        name, 
        email
    FROM 
        teachers
    WHERE 
        id = teacher_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTeacherQuizzes` (IN `teacher_id` INT)   BEGIN
    SELECT 
        q.id,
        q.title,
        q.subject,
        -- Count the number of questions in each quiz
        (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count,
        -- Count the number of students who have taken this quiz
        (SELECT COUNT(DISTINCT qr.student_id) FROM quiz_results qr WHERE qr.quiz_id = q.id) AS students_taken,
        -- Get the quiz status (assuming you have a status column; if not, use 'ongoing' as default)
        IFNULL(q.status, 'ongoing') AS status
    FROM 
        quizzes q
    WHERE 
        q.teacher_id = teacher_id
    ORDER BY 
        q.created_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTeacherQuizzesForFilter` (IN `teacher_id_param` INT)   BEGIN
    SELECT 
        id,
        title
    FROM 
        quizzes
    WHERE 
        teacher_id = teacher_id_param
    ORDER BY 
        title;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTeachers` ()   BEGIN
    SELECT 
        t.teacher_id,
        t.first_name,
        t.last_name,
        t.email,
        t.profile_image,
        t.department,
        COUNT(DISTINCT s.subject_id) AS subject_count
    FROM 
        teachers t
    LEFT JOIN 
        subjects s ON t.teacher_id = s.teacher_id
    GROUP BY 
        t.teacher_id
    ORDER BY 
        t.last_name, t.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTeacherStudentScores` (IN `p_teacher_id` INT)   BEGIN
    SELECT 
        s.name AS student_name,
        q.title AS quiz_title,
        q.subject,
        qr.score,
        qr.taken_at
    FROM 
        quiz_results qr
    JOIN 
        students s ON qr.student_id = s.id
    JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        q.teacher_id = p_teacher_id
    ORDER BY 
        qr.taken_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetTotalQuestions` (IN `p_quiz_id` INT)   BEGIN
    SELECT COUNT(*) AS total_questions
    FROM quiz_questions
    WHERE quiz_id = p_quiz_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegisterStudent` (IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO students (name, email, password, created_at)
    VALUES (p_name, p_email, p_password, NOW());
    
    SELECT LAST_INSERT_ID() AS student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegisterTeacher` (IN `p_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_password` VARCHAR(255))   BEGIN
    INSERT INTO teachers (name, email, password, created_at)
    VALUES (p_name, p_email, p_password, NOW());
    
    SELECT LAST_INSERT_ID() AS teacher_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchStudents` (IN `search_term` VARCHAR(100))   BEGIN
    SELECT 
        s.id,
        s.student_id,
        s.first_name,
        s.last_name,
        s.email,
        s.year_level,
        s.is_active,
        COUNT(qr.id) AS quizzes_taken,
        IFNULL(AVG((qr.score / (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id)) * 100), 0) AS avg_score
    FROM 
        students s
    LEFT JOIN 
        quiz_results qr ON s.id = qr.student_id
    LEFT JOIN 
        quizzes q ON qr.quiz_id = q.id
    WHERE 
        s.first_name LIKE CONCAT('%', search_term, '%')
        OR s.last_name LIKE CONCAT('%', search_term, '%')
        OR s.student_id LIKE CONCAT('%', search_term, '%')
        OR s.email LIKE CONCAT('%', search_term, '%')
        OR CONCAT(s.first_name, ' ', s.last_name) LIKE CONCAT('%', search_term, '%')
    GROUP BY 
        s.id
    ORDER BY 
        s.last_name, s.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SearchStudentsForTeacher` (IN `teacher_id_param` INT, IN `search_term` VARCHAR(100))   BEGIN
    SELECT 
        s.id,
        s.name,
        s.email,
        s.grade,
        COUNT(DISTINCT qr.id) AS quizzes_taken,
        IFNULL(AVG((qr.score / qr.total) * 100), 0) AS avg_score
    FROM 
        students s
    LEFT JOIN 
        quiz_results qr ON s.id = qr.student_id
    LEFT JOIN 
        quizzes q ON qr.quiz_id = q.id AND q.teacher_id = teacher_id_param
    WHERE 
        (s.name LIKE CONCAT('%', search_term, '%') OR
        s.email LIKE CONCAT('%', search_term, '%') OR
        s.grade LIKE CONCAT('%', search_term, '%'))
    GROUP BY 
        s.id, s.name, s.email, s.grade
    ORDER BY 
        s.name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SelectSubject` (IN `p_quiz_id` INT, IN `p_subject` VARCHAR(100))   BEGIN
    UPDATE quizzes
    SET subject = p_subject
    WHERE id = p_quiz_id;
    
    SELECT ROW_COUNT() AS rows_updated;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateQuizStatus` (IN `p_quiz_id` INT, IN `p_status` VARCHAR(20), IN `p_teacher_id` INT)   BEGIN
    DECLARE quiz_exists INT DEFAULT 0;
    
    -- First verify this quiz belongs to the teacher
    SELECT COUNT(*) INTO quiz_exists
    FROM quizzes 
    WHERE id = p_quiz_id AND teacher_id = p_teacher_id;
    
    IF quiz_exists = 0 THEN
        SELECT FALSE AS success, 'Quiz not found or access denied' AS message;
    ELSE
        -- Update the quiz status
        UPDATE quizzes 
        SET status = p_status 
        WHERE id = p_quiz_id;
        
        IF ROW_COUNT() > 0 THEN
            SELECT TRUE AS success, 'Quiz status updated successfully' AS message;
        ELSE
            SELECT FALSE AS success, 'No changes made' AS message;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateStudentGrade` (IN `p_student_id` INT, IN `p_grade` VARCHAR(20))   BEGIN
    UPDATE students 
    SET grade = p_grade 
    WHERE id = p_student_id;
    
    -- Return success status
    SELECT ROW_COUNT() > 0 AS success;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateTeacherSubject` (IN `p_teacher_id` INT, IN `p_subject` VARCHAR(100))   BEGIN
    UPDATE teachers 
    SET subject = p_subject 
    WHERE id = p_teacher_id;
    
    -- Return success status
    SELECT ROW_COUNT() > 0 AS success;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `teacher_id`, `title`, `subject`, `created_at`, `status`) VALUES
(29, 9, 'Test', 'Mathematics', '2025-06-06 03:11:25', 'ongoing'),
(30, 9, 'Quiz', 'Mathematics', '2025-06-06 03:25:06', 'ongoing'),
(31, 9, 'Quiz', 'English', '2025-06-06 03:32:56', 'ongoing'),
(39, 12, 'Basic MATH', 'Mathematics', '2025-06-08 20:09:06', 'ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `correct_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `quiz_id`, `question`, `correct_answer`) VALUES
(2, 29, '1 + 1', '2'),
(3, 30, '1+1', '2'),
(4, 30, '3+3', '6'),
(5, 30, '4+4', '8'),
(6, 31, 'What is the english of mansanas?', 'Apple'),
(7, 39, '1+1', '2');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_results`
--

CREATE TABLE `quiz_results` (
  `id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `taken_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_results`
--

INSERT INTO `quiz_results` (`id`, `quiz_id`, `student_id`, `score`, `total`, `taken_at`) VALUES
(1, 29, 6, 1, 1, '2025-06-06 03:17:13'),
(2, 30, 6, 3, 3, '2025-06-06 03:25:19'),
(3, 31, 6, 1, 1, '2025-06-06 03:33:03'),
(4, 31, 7, 1, 1, '2025-06-06 03:36:05'),
(5, 31, 8, 1, 1, '2025-06-07 22:09:09'),
(6, 30, 8, 3, 3, '2025-06-07 22:09:22'),
(7, 29, 8, 1, 1, '2025-06-07 22:09:28');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `grade` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `grade`, `password`, `created_at`) VALUES
(1, 'Strawberry Cheesecake', 'test@gmail.com', '7', '$2y$10$8IidABl81c8kBm3lHK1g0ulzH7Fseu65F.XZJn5ItlC4EpMv0lNBK', '2025-06-05 13:38:28'),
(6, 'Shaznay Eubra', 'naynay@gmail.com', '10', '$2y$10$0FCYsmgatEHVdQdQmAFHCuTDh63uliPpG//QkcyTcfpjBQlrpLWv6', '2025-06-05 14:24:59'),
(7, 'Kurt Borbe', 'kurt@gmail.com', '7', '$2y$10$8dE1z8acJIZuyrW9DTVXUO69AHj4.iqvkGsVJX0xi4u7oeM9hGV66', '2025-06-05 19:35:40'),
(8, 'Luis', 'ellay@gmail.com', '10', '$2y$10$MP9/8GttrvRp11Ym1nLEQOsD2xd0G1ZQjKIyeGeUTsPy55jlMNxEe', '2025-06-07 13:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `email`, `subject`, `password`, `created_at`) VALUES
(1, 'Strawberry Cheesecake', 'test@gmail.com', 'English', '$2y$10$/NUiPUhb6CiiTSjgTg7H/Ok5Mfq7JqAOWuxEq313p10lRyZs9ClOi', '2025-06-05 13:37:58'),
(2, 'dasdasd', 'adadasd@gmail.com', 'sdadasd', '$2y$10$CnHNDSp4.3n1DheKdGTHmeqJS7RmOvShiowNR/1QJWknPDYrGnHvO', '2025-06-05 13:39:57'),
(9, 'Strawberry Cheesecake', 'mayumi@gmail.com', 'English', '$2y$10$fUDmMigojUhUKuyzw5jukOrj/7XGF49CY2cqcOXHp0jjqJLTBA6NW', '2025-06-05 14:05:41'),
(11, 'Blueberry Cheesecake', 'cheese@gmail.com', 'English', '$2y$10$I/eOwmH8f1ccnjOdNb.PTOTh1PL5xtHCOZL.ViKIStsTDIrb.usKi', '2025-06-05 15:41:44'),
(12, 'Jayson', 'jayson@gmail.com', 'OOP', '$2y$10$PrB0/aWylj6jhcqG9ERB3.fIHOmdD0U/GnhMP9escxxm0RuNVpO.a', '2025-06-07 14:13:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_id` (`quiz_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quiz_results`
--
ALTER TABLE `quiz_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_results`
--
ALTER TABLE `quiz_results`
  ADD CONSTRAINT `quiz_results_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_results_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
