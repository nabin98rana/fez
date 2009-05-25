create table %TABLE_PREFIX%statistics_sum_yearmonth_figures
(
	syf_year int(4) not null,
	syf_monthnum int(2) not null,
	syf_month char(3) not null,
	syf_abstracts int not null,
	syf_downloads int not null,
	primary key(syf_year, syf_monthnum)
);
