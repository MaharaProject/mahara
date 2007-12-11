#!/bin/sh
set -e

print_usage() {
    echo "Usage is release.sh major minor"
    echo "e.g. release.sh 0.6 2"
    echo ""
    exit 1;
}

MAJOR="$1"
MINOR="$2"
BUILDDIR="/tmp/mahara_release"
CURRENTDIR="`pwd`"

if [ -z "${MAJOR}" ] || [ -z "${MINOR}" ]; then
    print_usage;
fi

if [ -d ${BUILDDIR} ]; then
    rm -rf ${BUILDDIR}
fi

mkdir ${BUILDDIR}

pushd ${BUILDDIR}

# Get the stable branch
git clone -n "http://git.catalyst.net.nz/mahara.git" mahara

pushd ${BUILDDIR}/mahara

# Switch to the release tag
#git checkout -b "${MAJOR}_STABLE" "origin/${MAJOR}_STABLE"
git checkout "${MAJOR}.${MINOR}_RELEASE"

# Remove git directories
rm .git -rf
rm debian -rf

# Pack MochiKit
scripts/pack.sh

popd

mv mahara mahara-${MAJOR}.${MINOR}

tar zcf ${CURRENTDIR}/mahara-${MAJOR}.${MINOR}.tar.gz mahara-${MAJOR}.${MINOR}
tar jcf ${CURRENTDIR}/mahara-${MAJOR}.${MINOR}.tar.bz2 mahara-${MAJOR}.${MINOR}
zip -qrT9 ${CURRENTDIR}/mahara-${MAJOR}.${MINOR}.zip mahara-${MAJOR}.${MINOR}

popd
#rm -rf ${BUILDDIR}
