----------------------------------- common schema ------------------------------
DROP TABLE  IF EXISTS common.users CASCADE;
DROP TABLE  IF EXISTS common.student CASCADE;
DROP TYPE   IF EXISTS common.youUserRole CASCADE;
DROP TYPE   IF EXISTS common.youClassName CASCADE;
DROP TYPE   IF EXISTS common.youSchemaName CASCADE;

DROP SCHEMA IF EXISTS common CASCADE;
CREATE SCHEMA IF NOT EXISTS common;

CREATE TYPE common.youUserRole  AS ENUM ('teacher', 'student');
CREATE TYPE common.youClassName AS ENUM ('901', '902', '903', '904', 'demo');
CREATE TYPE common.youSchemaName AS ENUM ('ohs_shao', 'demo');

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
   PRIMARY KEY(student_id)
);

----------------------------------- ohs_shao schema ------------------------------
DROP TABLE  IF EXISTS ohs_shao.breaks CASCADE;
DROP TABLE  IF EXISTS ohs_shao.notes CASCADE;
DROP TYPE   IF EXISTS ohs_shao.youBreakType CASCADE;
DROP TYPE   IF EXISTS ohs_shao.youPassType CASCADE;

DROP SCHEMA IF EXISTS ohs_shao CASCADE;
CREATE SCHEMA IF NOT EXISTS ohs_shao;

CREATE TYPE ohs_shao.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other', 'L w/o P', 'L w/o C');
CREATE TYPE ohs_shao.youPassType  AS ENUM ('A', 'B', 'Water', 'S1', 'S2', 'S3', 'L1', 'L2', 'L3');

CREATE TABLE IF NOT EXISTS ohs_shao.breaks (
   break_id    serial,
   student_id  INT,
   break_type  ohs_shao.youBreakType,
   pass_type   ohs_shao.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id)
);

CREATE TABLE IF NOT EXISTS ohs_shao.notes (
   note_id   serial,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

----------------------------------- demo schema ------------------------------
DROP TABLE  IF EXISTS demo.breaks CASCADE;
DROP TABLE  IF EXISTS demo.notes CASCADE;
DROP TYPE   IF EXISTS demo.youBreakType CASCADE;
DROP TYPE   IF EXISTS demo.youPassType CASCADE;

DROP SCHEMA IF EXISTS demo CASCADE;
CREATE SCHEMA IF NOT EXISTS demo;

CREATE TYPE demo.youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other');
CREATE TYPE demo.youPassType  AS ENUM ('A', 'B', 'C');

CREATE TABLE IF NOT EXISTS demo.breaks (
   break_id    serial,
   student_id  INT,
   break_type  demo.youBreakType,
   pass_type   demo.youPassType,
   time_out    TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in     TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES common.student(student_id)
);

CREATE TABLE IF NOT EXISTS demo.notes (
   note_id   serial,
   note_body TEXT NOT NULL,
   ts        TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   class     common.youClassName,
   PRIMARY KEY(note_id)
);

