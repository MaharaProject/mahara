all: build

clean:
	fakeroot make -f debian/rules clean

build: mochikit_packed
	rm -f ../mahara-apache2_*.deb ../mahara-apache_*.deb
	dpkg-buildpackage -rfakeroot -us -uc -b -tc

debug: mochikit_packed
	dpkg-buildpackage -rfakeroot -us -uc -b

mochikit_packed:
	scripts/pack.sh

.PHONY: build
