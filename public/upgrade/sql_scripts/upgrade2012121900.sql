/*
EXAMPLE BYPASS UPGRADE OF QUICK AUTH TEMPLATES
but you will need to implement this yourselves manually per fez instance install by setting a policy on a pid with this first
then SELECT * FROM fez_auth_index2 WHERE authi_pid = 'UQ:238624' and copying the role and group id into sql queries like below:

insert into %TABLE_PREFIX%auth_quick_rules_id values (1, 'UQ staff and students view only');
insert into %TABLE_PREFIX%auth_quick_rules values (1, 10, 55776);

insert into %TABLE_PREFIX%auth_quick_rules_id values (2, 'Fully Embargoed (system admins only)');
insert into %TABLE_PREFIX%auth_quick_rules values (2, 10, 14778);


insert into %TABLE_PREFIX%auth_quick_rules_id values (3, 'Only Thesis Office Approve, View, List. Printery View.');
insert into %TABLE_PREFIX%auth_quick_rules values (3, 2, 3057);
insert into %TABLE_PREFIX%auth_quick_rules values (3, 7, 3064);
insert into %TABLE_PREFIX%auth_quick_rules values (3, 8, 3131);
insert into %TABLE_PREFIX%auth_quick_rules values (3, 9, 3131);
insert into %TABLE_PREFIX%auth_quick_rules values (3, 10, 3131);

insert into %TABLE_PREFIX%auth_quick_rules_id values (4, 'Only SBS Theses Approve, View, List.');
insert into %TABLE_PREFIX%auth_quick_rules values (4, 2, 3303);
insert into %TABLE_PREFIX%auth_quick_rules values (4, 7, 3303);
insert into %TABLE_PREFIX%auth_quick_rules values (4, 8, 3303);
insert into %TABLE_PREFIX%auth_quick_rules values (4, 9, 55777);
insert into %TABLE_PREFIX%auth_quick_rules values (4, 10, 11);

insert into %TABLE_PREFIX%auth_quick_rules_id values (5, 'ERA Assessors only');
insert into %TABLE_PREFIX%auth_quick_rules values (5, 9, 55778);
insert into %TABLE_PREFIX%auth_quick_rules values (5, 10, 55778);

insert into %TABLE_PREFIX%auth_quick_rules_id values (6, 'UQ staff and students and printery view only');
insert into %TABLE_PREFIX%auth_quick_rules values (6, 9, 11);
insert into %TABLE_PREFIX%auth_quick_rules values (6, 10, 55779);

insert into %TABLE_PREFIX%auth_quick_rules_id values (7, 'Inherit from above');
insert into %TABLE_PREFIX%auth_quick_rules values (7, 9, 55457;
insert into %TABLE_PREFIX%auth_quick_rules values (7, 10, 11);

insert into %TABLE_PREFIX%auth_quick_rules_id values (8, 'Admin and UPO access only');
insert into %TABLE_PREFIX%auth_quick_rules values (8, 9, 11;
insert into %TABLE_PREFIX%auth_quick_rules values (8, 10, 55780);

insert into %TABLE_PREFIX%auth_quick_rules_id values (9, 'Open Access');
insert into %TABLE_PREFIX%auth_quick_rules values (9, 9, 11;
insert into %TABLE_PREFIX%auth_quick_rules values (9, 10, 11);

*/