create table %TABLE_PREFIX%statistics_sum_year
(
	syr_year char(4) not null,
	syr_pid varchar(64) not null,
	syr_title varchar(255) not null,
	syr_downloads int not null,
	syr_citation text,
	primary key (syr_year, syr_pid)
);