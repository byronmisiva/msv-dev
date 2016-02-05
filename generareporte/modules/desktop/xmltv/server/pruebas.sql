SELECT xmlSELECT tv_schedules.id,
                                xmltv_schedules.id_channel,
                                xmltv_schedules.id_programme,
                                  xmltv_programme.title,
                                   IF(length(xmltv_schedules.description) > 0, CONCAT(xmltv_programme.desc, ', ' , xmltv_schedules.desc) , xmltv_programme.desc ) AS desc,
                                  xmltv_programme.category,
DAYOFWEEK(xmltv_schedules.creado),
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', horario)) AS INT), '000') as inicio,
                                CONCAT(CAST(UNIX_TIMESTAMP (CONCAT(CURDATE(), ' ', horario)) AS INT) + xmltv_schedules.duracion * 60, '000') as fin,
                                xmltv_schedules.duracion,
                                xmltv_programme.file
                            FROM xmltv_schedules INNER JOIN xmltv_programme ON xmltv_schedules.id_programa = xmltv_programme.id
                WHERE IF(xmltv_schedules.fecha_fin IS NOT NULL, xmltv_schedules.fecha_inicio < CURDATE() AND CURDATE() < xmltv_schedules.fecha_fin , xmltv_schedules.fecha_inicio < CURDATE()) AND
                xmltv_programme.activo = 1;
