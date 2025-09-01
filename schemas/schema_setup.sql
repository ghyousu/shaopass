----------------------------------- common schema ------------------------------
DROP TABLE  IF EXISTS common.users CASCADE;
DROP TABLE  IF EXISTS common.student CASCADE;
DROP TYPE   IF EXISTS common.youUserRole CASCADE;
DROP TYPE   IF EXISTS common.youClassName CASCADE;
DROP TYPE   IF EXISTS common.youSchemaName CASCADE;
DROP TYPE   IF EXISTS common.commentType CASCADE;
DROP TYPE   IF EXISTS common.studentDisplayBgColor CASCADE;
DROP TYPE   IF EXISTS common.hwSubmissionStatus CASCADE;

DROP SCHEMA IF EXISTS common CASCADE;
CREATE SCHEMA IF NOT EXISTS common;

CREATE TYPE common.youUserRole  AS ENUM ('teacher', 'student');
CREATE TYPE common.youClassName AS ENUM ('G04', 'G06', 'G08', 'G09', 'demo');
CREATE TYPE common.youSchemaName AS ENUM ('shao', 'demo');
CREATE TYPE common.commentType  AS ENUM ('warning', 'reward');
CREATE TYPE common.studentDisplayBgColor AS ENUM ('unset', 'red', 'green', 'orange');
CREATE TYPE common.hwSubmissionStatus AS ENUM ('incomplete', 'semi-complete', 'completed');

CREATE TABLE IF NOT EXISTS common.users(
   user_name   VARCHAR(100) NOT NULL,
   pw          VARCHAR(255) NOT NULL,
   role        common.youUserRole NOT NULL,
   auth_class  common.youClassName NOT NULL,
   schema_name common.youSchemaName NOT NULL,
   PRIMARY KEY(user_name)
);

CREATE TABLE IF NOT EXISTS common.student (
   student_id serial,
   fname VARCHAR(50) NOT NULL,
   lname VARCHAR(50) NOT NULL,
   class common.youClassName NOT NULL,
   display_color common.studentDisplayBgColor DEFAULT 'unset',
   UNIQUE(fname, lname),
   PRIMARY KEY(student_id)
);

----------------------------------- shao schema ------------------------------
DROP TABLE  IF EXISTS shao.seating CASCADE;
DROP TABLE  IF EXISTS shao.breaks CASCADE;
DROP TABLE  IF EXISTS shao.notes CASCADE;
DROP TABLE  IF EXISTS shao.hw_submissions CASCADE;
DROP TABLE  IF EXISTS shao.comment_template;
DROP TABLE  IF EXISTS shao.teacherComment CASCADE;
DROP TYPE   IF EXISTS shao.youBreakType CASCADE;
DROP TYPE   IF EXISTS shao.youPassType CASCADE;

DROP SCHEMA IF EXISTS shao CASCADE;
CREATE SCHEMA IF NOT EXISTS shao;

CREATE TYPE shao.youBreakType AS ENUM ('Bathroom', 'Main Office', 'Nurse', 'Other');

CREATE TYPE shao.youPassType  AS ENUM ('Hallway', 'TBD A', 'TBD B');

CREATE TABLE IF NOT EXISTS shao.seating (
   student_id  INT,
   row SMALLINT,
   col SMALLINT,
   UNIQUE(student_id, row, col),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS shao.hw_submissions (
   hw_submission_id INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   hw_status common.hwSubmissionStatus NOT NULL DEFAULT 'incomplete',
   hw_date DATE NOT NULL DEFAULT CURRENT_DATE,
   PRIMARY KEY(hw_submission_id),
   unique(student_id, hw_date),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS shao.breaks (
   break_id    INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   break_type  shao.youBreakType,
   pass_type   shao.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS shao.notes (
   note_id   INT GENERATED ALWAYS AS IDENTITY,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

CREATE TABLE IF NOT EXISTS shao.comment_template (
   comment_id serial,
   cmt_type common.commentType NOT NULL,
   comment VARCHAR(512)
);
INSERT INTO shao.comment_template (cmt_type, comment) VALUES
('warning', 'talking in class'),
('warning', 'disturbing class'),
('warning', 'talk over teachers'),
('warning', 'disrespect teacher or classmate'),
('warning', 'play fighting'),
('warning', 'left classroom without permission');

CREATE TABLE IF NOT EXISTS shao.teacherComment (
   comment_id serial,
   student_id INT NOT NULL,
   teacher_name VARCHAR(100) NOT NULL,
   cmt_type common.commentType NOT NULL,
   comment VARCHAR(512),
   is_active boolean NOT NULL DEFAULT TRUE,
   time TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   redeem_time TIMESTAMPTZ DEFAULT NOW(),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE,
   FOREIGN KEY(teacher_name) REFERENCES common.users(user_name) ON DELETE CASCADE
);

----------------------------------- demo schema ------------------------------
DROP TABLE  IF EXISTS demo.seating CASCADE;
DROP TABLE  IF EXISTS demo.breaks CASCADE;
DROP TABLE  IF EXISTS demo.notes CASCADE;
DROP TABLE  IF EXISTS demo.comment_template;
DROP TABLE  IF EXISTS demo.teacherComment CASCADE;
DROP TYPE   IF EXISTS demo.youBreakType CASCADE;
DROP TYPE   IF EXISTS demo.youPassType CASCADE;

DROP SCHEMA IF EXISTS demo CASCADE;
CREATE SCHEMA IF NOT EXISTS demo;

CREATE TYPE demo.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other');
CREATE TYPE demo.youPassType  AS ENUM ('A', 'B', 'C');

CREATE TABLE IF NOT EXISTS demo.seating (
   student_id  INT,
   row SMALLINT,
   col SMALLINT,
   UNIQUE(student_id, row, col),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS demo.breaks (
   break_id    INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   break_type  demo.youBreakType,
   pass_type   demo.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS demo.notes (
   note_id   INT GENERATED ALWAYS AS IDENTITY,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

CREATE TABLE IF NOT EXISTS demo.comment_template (
   comment_id serial,
   cmt_type common.commentType NOT NULL,
   comment VARCHAR(512)
);

CREATE TABLE IF NOT EXISTS demo.teacherComment (
   comment_id serial,
   student_id INT NOT NULL,
   teacher_name VARCHAR(100) NOT NULL,
   cmt_type common.commentType NOT NULL,
   comment VARCHAR(512),
   is_active boolean NOT NULL DEFAULT TRUE,
   time TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   redeem_time TIMESTAMPTZ DEFAULT NOW(),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE,
   FOREIGN KEY(teacher_name) REFERENCES common.users(user_name) ON DELETE CASCADE
);
