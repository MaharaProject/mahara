#!/bin/bash

BUILDDIR="/tmp/mahara_release"
REPODIR="/tmp/mahara_repo"
ARCHLIST="i386 amd64"
DATE="`date`"

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
for release in stable unstable; do
    mkdir -p ${REPODIR}/dists/${release}/mahara
    pushd ${REPODIR}/dists/${release}/mahara
    mkdir binary-all
    for arch in ${ARCHLIST}; do mkdir binary-${arch}; done
    popd
    mkdir -p ${REPODIR}/pool/${release}
done

git-fetch -t
STABLE_RELEASE="`ls -1 .git/refs/tags | grep 'RELEASE$' | tail -n1`"

pushd ${BUILDDIR}

echo "Building ${STABLE_RELEASE} ..."
# Build Stable
cg-clone "git+ssh://git.catalyst.net.nz/git/public/mahara.git#${STABLE_RELEASE}"
rm -f *.deb
pushd ${BUILDDIR}/mahara
make
popd
cp *.deb ${REPODIR}/pool/stable/
rm ${BUILDDIR}/mahara -rf

echo "Building Trunk ..."
# Build Trunk
cg-clone "git+ssh://git.catalyst.net.nz/git/public/mahara.git"
rm -f *.deb
pushd ${BUILDDIR}/mahara
make
popd
cp *.deb ${REPODIR}/pool/unstable/
rm ${BUILDDIR}/mahara -rf

# Link other arches into all and build packages
for release in stable unstable; do
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

rsync -PIlvr --delete-after --no-p --no-g --chmod=Dg+ws,Fg+w ${REPODIR}/* locke.catalyst.net.nz:/home/ftp/pub/mahara/

