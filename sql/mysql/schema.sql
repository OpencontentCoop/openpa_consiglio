CREATE TABLE `openpa_consiglio_presenza` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seduta_id` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_bin NOT NULL,
  `in_out` int(1) NOT NULL,
  `created_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `openpa_consiglio_presenza`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `seduta_id` (`seduta_id`);

ALTER TABLE `openpa_consiglio_presenza`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


CREATE TABLE `openpaconsiglionotificationitem` (
  `id` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_time` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8_bin NOT NULL,
  `subject` varchar(250) COLLATE utf8_bin NOT NULL,
  `body` text COLLATE utf8_bin NOT NULL,
  `expected_send_time` int(11) NOT NULL,
  `sent` int(1) NOT NULL,
  `sent_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `openpaconsiglionotificationitem`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `openpaconsiglionotificationitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


CREATE TABLE `openpa_consiglio_voto` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seduta_id` int(11) NOT NULL,
  `votazione_id` int(11) NOT NULL,
  `value` varchar(100) COLLATE utf8_bin NOT NULL,
  `anomaly` int(1) NOT NULL,
  `presenza_id` int(11) NOT NULL,
  `created_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `openpa_consiglio_voto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `votazione_id` (`votazione_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seduta_id` (`seduta_id`);

ALTER TABLE `openpa_consiglio_voto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
