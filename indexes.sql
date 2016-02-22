CREATE INDEX i_namespace ON page (namespace);
CREATE INDEX i_page ON revision (page);
CREATE INDEX i_contrib ON revision (contrib);
CREATE FULLTEXT INDEX i_search ON page (search);
