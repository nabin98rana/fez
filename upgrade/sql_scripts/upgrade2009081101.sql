
CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%geocode_country (
  gctry_country_code char(2) NOT NULL,
  gctry_latitude double default NULL,
  gctry_longitude double default NULL,
  PRIMARY KEY  (gctry_country_code)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%geocode_regions (
  gcr_country_code char(2) NOT NULL,
  gcr_region_code char(2) NOT NULL,
  gcr_location_name varchar(100) NOT NULL,
  gcr_latitude double default NULL,
  gcr_longitude double default NULL,
  PRIMARY KEY  (gcr_country_code,gcr_region_code),
  KEY region_lat_lng_idx (gcr_latitude,gcr_longitude)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%geocode_cities (
  gcity_country_code char(2) NOT NULL,
  gcity_region_code char(2) NOT NULL,
  gcity_location_name varchar(100) NOT NULL,
  gcity_city varchar(100) NOT NULL,
  gcity_latitude double default NULL,
  gcity_longitude double default NULL,
  PRIMARY KEY  (gcity_country_code,gcity_region_code,gcity_location_name,gcity_city),
  KEY cities_lat_lng_idx (gcity_latitude,gcity_longitude)
);

CREATE TABLE IF NOT EXISTS %TABLE_PREFIX%geocode_location_cache (
  loc_location varchar(255) NOT NULL,
  loc_latitude double default NULL,
  loc_longitude double default NULL,
  loc_accuracy int(2) default NULL,
  PRIMARY KEY  (loc_location)
);
