DROP TYPE   IF EXISTS youUserRole CASCADE;
DROP TABLE  IF EXISTS ohs_shao.classes CASCADE;
DROP TABLE  IF EXISTS ohs_shao.breaks CASCADE;
DROP TYPE   IF EXISTS youBreakType CASCADE;
DROP TYPE   IF EXISTS youPassType CASCADE;
DROP TABLE  IF EXISTS ohs_shao.student CASCADE;
DROP TYPE   IF EXISTS youClassName CASCADE;
DROP TABLE  IF EXISTS ohs_shao.users CASCADE;
DROP SCHEMA IF EXISTS ohs_shao CASCADE;

CREATE TYPE youUserRole  AS ENUM ('teacher', 'student');
CREATE TYPE youClassName AS ENUM ('901', '902', '903', '904');
CREATE TYPE youBreakType AS ENUM ('Bathroom', 'Water', 'Nurse', 'Other');
CREATE TYPE youPassType  AS ENUM ('A', 'B', 'C', 'S1', 'S2', 'S3', 'L1', 'L2', 'L3');

CREATE SCHEMA IF NOT EXISTS ohs_shao;

CREATE TABLE IF NOT EXISTS ohs_shao.users(
   user_name VARCHAR(100) NOT NULL,
   pw VARCHAR(255) NOT NULL,
   role youUserRole NOT NULL,
   auth_class youClassName NOT NULL,
   PRIMARY KEY(user_name)
);

CREATE TABLE IF NOT EXISTS ohs_shao.student (
   student_id INT GENERATED ALWAYS AS IDENTITY,
   fname VARCHAR(50) NOT NULL,
   lname VARCHAR(50) NOT NULL,
   class youClassName NOT NULL,
   PRIMARY KEY(student_id)
);

CREATE TABLE IF NOT EXISTS ohs_shao.breaks (
   break_id INT GENERATED ALWAYS AS IDENTITY,
   student_id INT,
   break_type youBreakType,
   pass_type youPassType,
   time_out TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in  TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES ohs_shao.student(student_id)
);
