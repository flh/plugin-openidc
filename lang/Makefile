INSTALLDIR = .
LANGUAGES = `find ./ -name "oauth2_*.po" -print | sed 's|^\./oauth2_\(.*\).po|\1|'`
DOMAINS = oauth2
PHP_SOURCES = $(shell find ../ -maxdepth 1 -name \*.php) \
              $(shell find ../lib/GaletteOpenIDC/ -name \*.php) \
              $(shell find ../templates -name \*.tpl)


all : extract mo

po :
	@echo "Generating PO files:"
	@for l in ${LANGUAGES}; do \
	  for d in ${DOMAINS}; do \
	    if [ -f $${d}_$${l}.po ]; then \
	      echo -n "  Updating $${d}_$${l}.po"; \
	      msgmerge -U $${d}_$${l}.po $$d.pot >/dev/null ; \
	    else \
	      echo "  Creating of $${d}_$${l}.po"; \
	      msginit -o $${d}_$${l}.po -i $$d.pot >/dev/null ; \
	    fi; \
	  done \
	done

mo :
	@echo "Generating MO files:"
	@for l in ${LANGUAGES}; do \
	  for d in ${DOMAINS}; do \
	    mkdir -p ${INSTALLDIR}/$${l}/LC_MESSAGES; \
	    echo "    formatting ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo."; \
	    msgfmt $${d}_$${l}.po -o ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new; \
	    if diff -qI 'PO-Revision-Date:.*' ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo > /dev/null; then \
	      echo "    $${d}.mo NOT updated."; \
	      rm ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new; \
	    else \
	      echo "    $${d}.mo UPDATED."; \
	      mv ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo.new ${INSTALLDIR}/$${l}/LC_MESSAGES/$${d}.mo; \
	    fi; \
	  done \
	done

extract : ${PHP_SOURCES} Makefile xgettext.py
	./xgettext.py ${PHP_SOURCES}
	@echo "Generating en_US:"
	@for d in ${DOMAINS}; do \
	  LANG=C msgen $$d.pot -o $${d}_en_US.po; \
	done
