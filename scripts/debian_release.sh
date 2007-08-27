#!/bin/bash
#
# Builds and releases Mahara to the debian repo
# 
# This script can release just one version (stable|testing|unstable), or all at
# once
#
set -e
BUILDDIR="/tmp/mahara_release"
REPODIR="/tmp/mahara_repo"
ARCHLIST="i386 amd64"
DATE="`date`"

RELEASE=$1
if [ "$RELEASE" = "" ]; then
    echo -n "Building ALL versions: are you sure? (y/N) "
    read ANS
    if [ "$ANS" != "y" ] && [ "$ANS" != "Y" ]; then
        echo "Abort."
        exit 1
    fi
    RELEASELIST="stable testing unstable"
elif [ "$RELEASE" != "stable" ] && [ "$RELEASE" != "testing" ] && [ "$RELEASE" != "unstable" ]; then
    echo "Invalid release: $RELEASE"
    exit 1
else
    RELEASELIST=$RELEASE
fi


print_usage() {
    echo "Usage is debian_release.sh"
    echo "(must be run from a trunk checkout of mahara)"
    echo ""
    exit 1;
}

if [ ! -d .git ]; then
    print_usage;
fi

if [ -d ${BUILDDIR} ]; then
    rm -rf ${BUILDDIR}
fi
if [ -d ${REPODIR} ]; then
    rm -rf ${REPODIR}
fi

mkdir ${BUILDDIR}

# Create repo dirs
for release in $RELEASELIST; do
    mkdir -p ${REPODIR}/dists/${release}/mahara
    pushd ${REPODIR}/dists/${release}/mahara
    mkdir binary-all
    for arch in ${ARCHLIST}; do mkdir binary-${arch}; done
    popd
    mkdir -p ${REPODIR}/pool/${release}
done

# If done properly, we don't need this here - the build can happen from any place that has this script
git-fetch -t
STABLE_RELEASE="`ls -1 .git/refs/tags | grep 'RELEASE$' | tail -n1`"
TESTING_RELEASE="`git branch -a | grep "origin.*STABLE$" | sort | tail -1 | sed 's/origin\///' | tr -d ' '`"

pushd ${BUILDDIR}

# Build Stable
if [ "$RELEASE" = "" ] || [ "$RELEASE" = "stable" ]; then
    echo
    echo "Building ${STABLE_RELEASE} ..."

    git clone -n "git+ssh://git.catalyst.net.nz/git/public/mahara.git" mahara
    ( cd mahara && git checkout -b "${STABLE_RELEASE}" "${STABLE_RELEASE}" )
    rm -f *.deb
    pushd ${BUILDDIR}/mahara
    make
    popd
    cp *.deb ${REPODIR}/pool/stable/
    rm ${BUILDDIR}/mahara -rf
fi

# Build Testing (tip of stable)
if [ "$RELEASE" = "" ] || [ "$RELEASE" = "testing" ]; then
    echo
    echo "Building Testing (${TESTING_RELEASE})..."

    git clone -n "git+ssh://git.catalyst.net.nz/git/public/mahara.git" mahara
    ( cd mahara && git checkout -b ${TESTING_RELEASE} "origin/${TESTING_RELEASE}" )
    rm -f *.deb
    pushd ${BUILDDIR}/mahara
    make
    popd
    cp *.deb ${REPODIR}/pool/testing/
    rm ${BUILDDIR}/mahara -rf
fi


# Build Unstable
if [ "$RELEASE" = "" ] || [ "$RELEASE" = "unstable" ]; then
    echo
    echo "Building Unstable ..."

    git clone "git+ssh://git.catalyst.net.nz/git/public/mahara.git" mahara
    ( cd mahara && git checkout master )
    rm -f *.deb
    pushd ${BUILDDIR}/mahara
    make
    popd
    cp *.deb ${REPODIR}/pool/unstable/
    rm ${BUILDDIR}/mahara -rf
fi

# Link other arches into all and build packages
for release in $RELEASELIST; do
    pushd ${REPODIR}/pool

    for arch in all ${ARCHLIST}; do
        dpkg-scanpackages ${release} /dev/null /pool/ | /bin/gzip -9 > ${REPODIR}/dists/${release}/mahara/binary-${arch}/Packages.gz
        dpkg-scanpackages ${release} /dev/null /pool/ > ${REPODIR}/dists/${release}/mahara/binary-${arch}/Packages
    done

    popd

    pushd ${REPODIR}/dists/${release}

    # Create Release file
    cat <<EOHDR >Release
Origin: Mahara
Label: Mahara
Suite: ${release}
Date: ${DATE}
Architectures: ${ARCHLIST}
Components: mahara
Description: Mahara ${release} repository
MD5Sum:
EOHDR
    
    for file in `find mahara -type f -name 'Packages*'`; do
        MD5="`md5sum $file | cut -c1-32`"
        SIZE="`cat $file | wc -c`"
        printf " %s %16d %s\n" "${MD5}" "${SIZE}" "${file}" >>Release
    done

    gpg --yes --armour --sign-with 1D18A55D --detach-sign --output Release.gpg Release

    popd
done

popd

cp debian/index.html ${REPODIR}

if [ "$RELEASE" = "" ]; then
    rsync -PIlvr --delete-after --no-p --no-g --chmod=Dg+ws,Fg+w ${REPODIR}/* locke.catalyst.net.nz:/home/ftp/pub/mahara/
else
    rsync -PIlvr --no-p --no-g --chmod=Dg+ws,Fg+w ${REPODIR}/* locke.catalyst.net.nz:/home/ftp/pub/mahara/
fi

