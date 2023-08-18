# typo3-ext-sysinfo
Extension for Typo3 v10 + v11. Helpful for preparing migration or maintaining security of an installation.
Information on plugins used in pages, robots.txt, sitemap.xml and security checks.

In detail:
- security: check some settings and files that should not be there or are altered
- plugins: find content usng specific PluinType or ContentType and links directly to edit content
- root templates: find all and show what they include statically
- all template: as above + find alltemplates
- walk over all domains from site configuration and see f we can access robots.txt, sitemap.xml
