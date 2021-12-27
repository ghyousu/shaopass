-- DROP SCHEMA IF EXISTS ohs_shao;
-- DROP TABLE  IF EXISTS ohs_shao.student;
-- DROP TABLE  IF EXISTS ohs_shao.breaks;

CREATE SCHEMA IF NOT EXISTS ohs_shao;

CREATE TABLE IF NOT EXISTS ohs_shao.student (
   student_id INT GENERATED ALWAYS AS IDENTITY,
   fname VARCHAR(255) NOT NULL,
   lname VARCHAR(255) NOT NULL,
   class VARCHAR(255) NOT NULL,
   PRIMARY KEY(student_id)
);

CREATE TABLE IF NOT EXISTS ohs_shao.breaks (
   break_id INT GENERATED ALWAYS AS IDENTITY,
   student_id INT,
   break_type VARCHAR(255),
   pass_type VARCHAR(255),
   time_out TIMESTAMPTZ NOT NULL DEFAULT NOW(),
   time_in  TIMESTAMPTZ DEFAULT NOW(),
   PRIMARY KEY(break_id),
   FOREIGN KEY(student_id) REFERENCES ohs_shao.student(student_id) ON DELETE CASACADE
);
