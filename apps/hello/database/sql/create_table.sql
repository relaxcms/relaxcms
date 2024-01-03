create table cms_hello (
  id int NOT NULL,
  name varchar(64) NOT NULL,
  description text NULL,
  photo varchar(256) NULL,
  video varchar(256) NULL,
  cuid int not null, 
  uid int not null, 
  ts bigint not null, 
  status int not null, 
  primary key(id)
);