all: build

clean:
	fakeroot make -f debian/rules clean

build:
	rm -f ../mahara-apache2_.deb ../mahara-apache_.deb
	dpkg-buildpackage -rfakeroot -us -uc -b -tc

debug:
	dpkg-buildpackage -rfakeroot -us -uc -b

.PHONY: build
