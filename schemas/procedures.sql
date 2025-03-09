-- auto update hw_status in ohs_shao.hw_submissions upon each procedure call
CREATE OR REPLACE PROCEDURE ohs_shao.toggleHomeworkStatus(stud_id INT)
language plpgsql
AS $$
DECLARE
   old_status common.hwSubmissionStatus;
   new_status common.hwSubmissionStatus;
BEGIN
   SELECT hw_status INTO old_status FROM ohs_shao.hw_submissions WHERE student_id = stud_id AND hw_date = CURRENT_DATE;

   IF not found THEN
      INSERT INTO ohs_shao.hw_submissions (student_id, hw_status) VALUES (stud_id, 'semi-complete');
   ELSE
      SELECT CASE
         WHEN old_status = 'incomplete' THEN 'semi-complete'
         WHEN old_status = 'semi-complete' THEN 'completed'
         WHEN old_status = 'completed' THEN 'incomplete'
      END INTO new_status;

      UPDATE ohs_shao.hw_submissions SET hw_status = new_status WHERE student_id = stud_id AND hw_date = CURRENT_DATE;
   END IF;

   COMMIT;
END;
$$;
