-- abned: 10/02/2025
-- get all specialities
-- step to import data
---- step 1: export the result of the query to CSV from prod
---- step 2: import the CSV to the target database by specifying the line to skip (1) and column name (seprated by comma)
SELECT 
    child.tid AS id,
    child.name AS 'name',
    parent.tid AS speciality_parent_id
FROM taxonomy_term_data AS child
LEFT JOIN taxonomy_term_hierarchy AS h ON child.tid = h.tid
LEFT JOIN taxonomy_term_data AS parent ON h.parent = parent.tid
INNER JOIN taxonomy_vocabulary AS v ON child.vid = v.vid
WHERE child.vid = 5 -- sp_cialit_
ORDER BY child.tid;

-- abned: 11/02/2025
-- get all regions
SELECT 
    child.tid AS id,
    child.name AS 'name'
FROM taxonomy_term_data AS child
WHERE child.vid = 6 -- r_gion
ORDER BY child.tid;

-- abned: 12/02/2025
-- get all roles
INSERT INTO `user_role` (`id`, `role`) VALUE(4, 'Remplaçant');
INSERT INTO `user_role` (`id`, `role`) VALUE(5, 'Clinique / Service clinique');
INSERT INTO `user_role` (`id`, `role`) VALUE(6, 'Cabinet');
INSERT INTO `user_role` (`id`, `role`) VALUE(7, 'Directeur');
INSERT INTO `user_role` (`id`, `role`) VALUE(3, 'Administrator');
INSERT INTO `user_role` (`id`, `role`) VALUE(8, 'Webmaster');