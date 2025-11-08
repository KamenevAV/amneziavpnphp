-- Add translations for monitoring UI elements

INSERT INTO translations (translation_key, language_code, translation_value) VALUES 
-- Speed
('common.speed', 'en', 'Speed'),
('common.speed', 'ru', 'Скорость'),
('common.speed', 'es', 'Velocidad'),
('common.speed', 'de', 'Geschwindigkeit'),
('common.speed', 'fr', 'Vitesse'),
('common.speed', 'zh', '速度'),

-- Metrics
('common.metrics', 'en', 'Metrics'),
('common.metrics', 'ru', 'Метрики'),
('common.metrics', 'es', 'Métricas'),
('common.metrics', 'de', 'Metriken'),
('common.metrics', 'fr', 'Métriques'),
('common.metrics', 'zh', '指标'),

-- Server Info
('servers.server_info', 'en', 'Server Info'),
('servers.server_info', 'ru', 'Информация о сервере'),
('servers.server_info', 'es', 'Información del servidor'),
('servers.server_info', 'de', 'Serverinformationen'),
('servers.server_info', 'fr', 'Informations sur le serveur'),
('servers.server_info', 'zh', '服务器信息'),

-- Status
('common.status', 'en', 'Status'),
('common.status', 'ru', 'Статус'),
('common.status', 'es', 'Estado'),
('common.status', 'de', 'Status'),
('common.status', 'fr', 'Statut'),
('common.status', 'zh', '状态'),

-- Client Configuration
('clients.configuration', 'en', 'Client Configuration'),
('clients.configuration', 'ru', 'Конфигурация клиента'),
('clients.configuration', 'es', 'Configuración del cliente'),
('clients.configuration', 'de', 'Client-Konfiguration'),
('clients.configuration', 'fr', 'Configuration du client'),
('clients.configuration', 'zh', '客户端配置'),

-- Traffic Statistics
('clients.traffic_stats', 'en', 'Traffic Statistics'),
('clients.traffic_stats', 'ru', 'Статистика трафика'),
('clients.traffic_stats', 'es', 'Estadísticas de tráfico'),
('clients.traffic_stats', 'de', 'Traffic-Statistiken'),
('clients.traffic_stats', 'fr', 'Statistiques de trafic'),
('clients.traffic_stats', 'zh', '流量统计'),

-- Uploaded
('common.uploaded', 'en', 'Uploaded'),
('common.uploaded', 'ru', 'Отправлено'),
('common.uploaded', 'es', 'Subido'),
('common.uploaded', 'de', 'Hochgeladen'),
('common.uploaded', 'fr', 'Envoyé'),
('common.uploaded', 'zh', '上传'),

-- Downloaded
('common.downloaded', 'en', 'Downloaded'),
('common.downloaded', 'ru', 'Получено'),
('common.downloaded', 'es', 'Descargado'),
('common.downloaded', 'de', 'Heruntergeladen'),
('common.downloaded', 'fr', 'Reçu'),
('common.downloaded', 'zh', '下载'),

-- Total
('common.total', 'en', 'Total'),
('common.total', 'ru', 'Всего'),
('common.total', 'es', 'Total'),
('common.total', 'de', 'Gesamt'),
('common.total', 'fr', 'Total'),
('common.total', 'zh', '总计'),

-- Created
('common.created', 'en', 'Created'),
('common.created', 'ru', 'Создан'),
('common.created', 'es', 'Creado'),
('common.created', 'de', 'Erstellt'),
('common.created', 'fr', 'Créé'),
('common.created', 'zh', '创建时间'),

-- IP Address
('common.ip_address', 'en', 'IP Address'),
('common.ip_address', 'ru', 'IP-адрес'),
('common.ip_address', 'es', 'Dirección IP'),
('common.ip_address', 'de', 'IP-Adresse'),
('common.ip_address', 'fr', 'Adresse IP'),
('common.ip_address', 'zh', 'IP地址')

ON DUPLICATE KEY UPDATE translation_value=VALUES(translation_value);
