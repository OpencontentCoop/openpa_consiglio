CREATE SEQUENCE openpa_consiglio_presenza_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

CREATE TABLE openpa_consiglio_presenza (
  id integer DEFAULT nextval('openpa_consiglio_presenza_s'::text) NOT NULL,
  user_id INTEGER DEFAULT NULL NOT NULL,
  seduta_id INTEGER DEFAULT NULL NOT NULL,
  type VARCHAR(50) DEFAULT NULL NOT NULL,
  in_out INTEGER DEFAULT NULL NOT NULL,
  created_time INTEGER DEFAULT 0
);

ALTER TABLE ONLY openpa_consiglio_presenza ADD CONSTRAINT openpa_consiglio_presenza_pkey PRIMARY KEY (id);

CREATE INDEX openpa_consiglio_presenza_type ON openpa_consiglio_presenza USING btree (type);
CREATE INDEX openpa_consiglio_presenza_user_id ON openpa_consiglio_presenza USING btree (user_id);
CREATE INDEX openpa_consiglio_presenza_seduta_id ON openpa_consiglio_presenza USING btree (seduta_id);

CREATE SEQUENCE openpaconsiglionotificationitem_s
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;

CREATE TABLE openpaconsiglionotificationitem
(
  id integer NOT NULL DEFAULT nextval('openpaconsiglionotificationitem_s'::regclass),
  object_id integer DEFAULT 0,
  user_id integer DEFAULT 0,
  created_time integer DEFAULT 0,
  type character varying(50),
  subject character varying(250),
  body text,
  expected_send_time integer DEFAULT 0,
  sent integer DEFAULT 0,
  sent_time integer DEFAULT 0,
  CONSTRAINT pk_id PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);

CREATE SEQUENCE openpa_consiglio_voto_s
    START 1
    INCREMENT 1
    MAXVALUE 9223372036854775807
    MINVALUE 1
    CACHE 1;

CREATE TABLE openpa_consiglio_voto (
  id integer DEFAULT nextval('openpa_consiglio_voto_s'::text) NOT NULL,
  user_id INTEGER DEFAULT NULL NOT NULL,
  seduta_id INTEGER DEFAULT NULL NOT NULL,
  votazione_id VARCHAR(50) DEFAULT NULL NOT NULL,
  value VARCHAR(100) DEFAULT NULL NOT NULL,
  anomaly INTEGER DEFAULT 0,
  presenza_id INTEGER DEFAULT 0,
  created_time INTEGER DEFAULT 0
);

ALTER TABLE ONLY openpa_consiglio_voto ADD CONSTRAINT openpa_consiglio_voto_pkey PRIMARY KEY (id);

CREATE INDEX openpa_consiglio_voto_votazione_id ON openpa_consiglio_voto USING btree (votazione_id);
CREATE INDEX openpa_consiglio_voto_user_id ON openpa_consiglio_voto USING btree (user_id);
CREATE INDEX openpa_consiglio_voto_seduta_id ON openpa_consiglio_voto USING btree (seduta_id);