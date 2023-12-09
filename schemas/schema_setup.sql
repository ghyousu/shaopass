----------------------------------- common schema ------------------------------
DROP TABLE  IF EXISTS common.users CASCADE;
DROP TABLE  IF EXISTS common.student CASCADE;
DROP TYPE   IF EXISTS common.youUserRole CASCADE;
DROP TYPE   IF EXISTS common.youClassName CASCADE;
DROP TYPE   IF EXISTS common.youSchemaName CASCADE;
DROP TYPE   IF EXISTS common.commentType CASCADE;
DROP TYPE   IF EXISTS common.studentDisplayBgColor CASCADE;

DROP SCHEMA IF EXISTS common CASCADE;
CREATE SCHEMA IF NOT EXISTS common;

CREATE TYPE common.youUserRole  AS ENUM ('teacher', 'student');
CREATE TYPE common.youClassName AS ENUM ('901', '902', '903', '904', 'demo');
CREATE TYPE common.youSchemaName AS ENUM ('ohs_shao', 'salim', 'ela', 'demo');
CREATE TYPE common.commentType  AS ENUM ('warning', 'reward');
CREATE TYPE common.studentDisplayBgColor AS ENUM ('unset', 'red', 'green', 'orange');

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

----------------------------------- ohs_shao schema ------------------------------
DROP TABLE  IF EXISTS ohs_shao.seating CASCADE;
DROP TABLE  IF EXISTS ohs_shao.breaks CASCADE;
DROP TABLE  IF EXISTS ohs_shao.notes CASCADE;
DROP TABLE  IF EXISTS ohs_shao.comment_template;
DROP TABLE  IF EXISTS ohs_shao.teacherComment CASCADE;
DROP TYPE   IF EXISTS ohs_shao.youBreakType CASCADE;
DROP TYPE   IF EXISTS ohs_shao.youPassType CASCADE;

DROP SCHEMA IF EXISTS ohs_shao CASCADE;
CREATE SCHEMA IF NOT EXISTS ohs_shao;

CREATE TYPE ohs_shao.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other', 'L w/o P', 'L w/o C');
CREATE TYPE ohs_shao.youPassType  AS ENUM ('A', 'B', 'Water', 'S1', 'S2', 'S3', 'L1', 'L2', 'L3');

CREATE TABLE IF NOT EXISTS ohs_shao.seating (
   student_id  INT,
   row SMALLINT,
   col SMALLINT,
   UNIQUE(student_id, row, col),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ohs_shao.breaks (
   break_id    INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   break_type  ohs_shao.youBreakType,
   pass_type   ohs_shao.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ohs_shao.notes (
   note_id   INT GENERATED ALWAYS AS IDENTITY,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

CREATE TABLE IF NOT EXISTS ohs_shao.teacherComment (
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

----------------------------------- salim schema ------------------------------
DROP TABLE  IF EXISTS salim.seating CASCADE;
DROP TABLE  IF EXISTS salim.breaks CASCADE;
DROP TABLE  IF EXISTS salim.notes CASCADE;
DROP TABLE  IF EXISTS salim.comment_template;
DROP TABLE  IF EXISTS salim.teacherComment CASCADE;
DROP TYPE   IF EXISTS salim.youBreakType CASCADE;
DROP TYPE   IF EXISTS salim.youPassType CASCADE;

DROP SCHEMA IF EXISTS salim CASCADE;
CREATE SCHEMA IF NOT EXISTS salim;

CREATE TYPE salim.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other', 'L w/o P', 'L w/o C');
CREATE TYPE salim.youPassType  AS ENUM ('A', 'B', 'Water', 'S1', 'S2', 'S3', 'L1', 'L2', 'L3');

CREATE TABLE IF NOT EXISTS salim.seating (
   student_id  INT,
   row SMALLINT,
   col SMALLINT,
   UNIQUE(student_id, row, col),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS salim.breaks (
   break_id    INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   break_type  salim.youBreakType,
   pass_type   salim.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS salim.notes (
   note_id   INT GENERATED ALWAYS AS IDENTITY,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

CREATE TABLE IF NOT EXISTS salim.teacherComment (
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

----------------------------------- ela schema ------------------------------
DROP TABLE  IF EXISTS ela.seating CASCADE;
DROP TABLE  IF EXISTS ela.breaks CASCADE;
DROP TABLE  IF EXISTS ela.notes CASCADE;
DROP TABLE  IF EXISTS ela.comment_template;
DROP TABLE  IF EXISTS ela.teacherComment CASCADE;
DROP TYPE   IF EXISTS ela.youBreakType CASCADE;
DROP TYPE   IF EXISTS ela.youPassType CASCADE;

DROP SCHEMA IF EXISTS ela CASCADE;
CREATE SCHEMA IF NOT EXISTS ela;

CREATE TYPE ela.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other', 'L w/o P', 'L w/o C');
CREATE TYPE ela.youPassType  AS ENUM ('A', 'B', 'Water', 'S1', 'S2', 'S3', 'L1', 'L2', 'L3');

CREATE TABLE IF NOT EXISTS ela.seating (
   student_id  INT,
   row SMALLINT,
   col SMALLINT,
   UNIQUE(student_id, row, col),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ela.breaks (
   break_id    INT GENERATED ALWAYS AS IDENTITY,
   student_id  INT,
   break_type  ela.youBreakType,
   pass_type   ela.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ela.notes (
   note_id   INT GENERATED ALWAYS AS IDENTITY,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

CREATE TABLE IF NOT EXISTS ela.teacherComment (
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
