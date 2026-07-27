SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

create table sfly_users_bonuses
(
	user_id BIGINT(20) not null,
	object_id BIGINT(20) not null,
    type VARCHAR(50) not null default 'order',
	bonuses DECIMAL(14,2) default 0.00 not null,
	sms TINYINT(1) default 0 not null,
	created TIMESTAMP default CURRENT_TIMESTAMP not null,
	last_modify TIMESTAMP default NULL null,
	constraint `PRIMARY`
		primary key (user_id, object_id, type)
);

create index bonuses
	on sfly_users_bonuses (bonuses);

create index sms
	on sfly_users_bonuses (sms);

create index created
	on sfly_users_bonuses (created);

COMMIT;
