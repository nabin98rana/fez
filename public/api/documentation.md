## API Documentation

#### author_publications.php

GET
* author_username<sup>*</sup>
* start_year
* end_year
* callback

#### create_author.php
Requires token in header X-API-TOKEN

POST
* title<sup>*</sup>
* fname<sup>*</sup>
* lname<sup>*</sup>
* dname
* org_staff_id
* org_student_id
* org_username
* mname
* position
* email
* homepage_link
* aut_ref_num
* researcher_id
* scopus_id
* google_scholar_id
* people_australia_id
* mypub_url
* description
* callback


#### flint.php
GET
* (languages = true || interviewees = true)<sup>*</sup>
* callback

#### ids.php
Requires token in header X-API-TOKEN

GET
* author_username<sup>*</sup>
* id
* id_type
* list
* callback

Orcid - grant = true
* grant
* value
* name
* expires
* status
* details

#### latest_metric_changes.php
GET
* author_username<sup>*</sup>
* callback

#### publons_reviews.php
Requires token in header X-API-TOKEN

GET
* author_username<sup>*</sup>
* start_year
* end_year
* callback