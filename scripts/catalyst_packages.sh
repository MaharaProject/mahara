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

print_usage() {
    echo "Usage: ./scripts/catalyst-packages.sh [stable|unstable]"
    echo "Run this script from the root directory of a Mahara checkout"
    echo ""
}

if [ ! -d .git ]; then
    print_usage
    exit 1
fi


echo " *** STOP *** "
echo " Make sure you have merged master into pkg-catalyst, and the latest"
echo " stable branch into the appropriate pkg-catalyst-* branch. If you"
echo " have not done this, hit Ctrl-C now and do so."
read junk

RELEASE=$1
if [ "$RELEASE" = "" ]; then
    echo -n "Building ALL versions: are you sure? (y/N) "
    read ANS
    if [ "$ANS" != "y" ] && [ "$ANS" != "Y" ]; then
        echo "Abort."
        exit 1
    fi
    RELEASELIST="stable unstable"
elif [ "$RELEASE" != "stable" ] && [ "$RELEASE" != "unstable" ]; then
    echo "Invalid release: $RELEASE"
    exit 1
else
    RELEASELIST=$RELEASE
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
STABLE_RELEASE="`ls -1 .git/refs/tags | egrep '[0-9]+\.[0-9]+\.[0-9]+_RELEASE$' | tail -n1`"
TESTING_RELEASE="`git branch -a | grep "origin.*STABLE$" | sort | tail -1 | sed 's/origin\///' | tr -d ' '`"
STABLE_DEBIAN_BRANCH=${STABLE_RELEASE:0:3}

pushd ${BUILDDIR}

# Build Stable
if [ "$RELEASE" = "" ] || [ "$RELEASE" = "stable" ]; then
    echo
    echo "Building ${STABLE_RELEASE} ..."

    git clone -n "git+ssh://git.catalyst.net.nz/git/public/mahara.git" mahara
    ( cd mahara && git checkout -b "pkg-catalyst-${STABLE_DEBIAN_BRANCH}" "origin/pkg-catalyst-${STABLE_DEBIAN_BRANCH}" )
    rm -f *.deb
    pushd ${BUILDDIR}/mahara
    make
    popd
    cp *.deb ${REPODIR}/pool/stable/
    rm ${BUILDDIR}/mahara -rf
fi

# Build Unstable
if [ "$RELEASE" = "" ] || [ "$RELEASE" = "unstable" ]; then
    echo
    echo "Building Unstable ..."

    git clone "git+ssh://git.catalyst.net.nz/git/public/mahara.git" mahara
    ( cd mahara && git checkout -b pkg-catalyst origin/pkg-catalyst )
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

# Steal the latest index.html and dump into 
git cat-file blob origin/pkg-catalyst:debian/index.html > ${REPODIR}/index.html

# Now (optionally) sync the repo to the git repository
echo " The repo has now been set up in ${REPODIR}. If you're really sure,"
echo " this script can rsync this to the git repository."
echo " rsync to git repository? [y/N] "
read ANS
if [ "$ANS" != "y" ] && [ "$ANS" != "Y" ]; then
    echo "Abort."
    exit 1
fi

if [ "$RELEASE" = "" ]; then
    rsync -PIlvr --delete-after --no-p --no-g --chmod=Dg+ws,Fg+w ${REPODIR}/* locke.catalyst.net.nz:/home/ftp/pub/mahara/
else
    rsync -PIlvr --no-p --no-g --chmod=Dg+ws,Fg+w ${REPODIR}/* locke.catalyst.net.nz:/home/ftp/pub/mahara/
fi

