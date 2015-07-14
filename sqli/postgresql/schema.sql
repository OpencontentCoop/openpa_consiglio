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

