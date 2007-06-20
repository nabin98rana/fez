-- Add "Community_Administrator" role to Create Record workflow

update `%TABLE_PREFIX%workflow`
set wfl_roles = 'Community_Administrator'
where wfl_title = 'Create Record';
