# GNUmakefile

SHELL = /bin/bash
DIST = 142ac3rc2
WDIR = 142ac3rc2_utf
MINIMIZER = /usr/local/bin/slimit 
WWWDIR = /var/www

.PHONY: trash_list.txt md5sum md5check jquery_check 

tags:
	etags --language=php `find . -type f -iname '*.php'`

# md5sum:
# 	md5sum `find . -type f ` > md5.sum 

# md5check:
# 	md5sum -c md5.sum 

dist:	jqcal.min.js
	pushd .. && tar zcvf $(DIST).tgz --exclude-from=$(WDIR)/exclude.def $(WDIR)/ \
       && md5sum  $(DIST).tgz > $(DIST).md5 && popd 


trash_list.txt: 
	find . -iname '*,*.php' > $@ 
	find . -iname '*.php.*' >> $@ 	
	find . -iname '*~' >> $@ 
	find . -iname '*.bak'>> $@ 

distclean: # trash_list.txt 
	echo "dry run; to really perform, try target realdistclean." 
	for afile in `cat trash_list.txt`; do \
	   echo "rm "$$afile ; \
	done  

realdistclean: # trash_list.txt 
	echo "cleaning useless file" 
	for afile in `cat trash_list.txt`; do \
	   rm $$afile ; \
	done  

jqcal.min.js: jqcal.js 
	$(MINIMIZER) $^ > $@ 

JQDEP:= 'js/jquery-1.10.2.min.js' 'js/jquery-ui-1.10.3.custom.min.js' 'development-bundle/ui/i18n/jquery.ui.datepicker-fr-iso8859-1.js' 
# 'notfound'
jquery_check: 
	for adep in $(JQDEP) ; do \
		ls $(WWWDIR)/jquery/$$adep ; \
	done 

# 	 $@@ $^
# $( ls /var/ );
# 	for afile in $( cat $< ); do echo $afile ; done  
