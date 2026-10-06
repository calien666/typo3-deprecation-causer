#!/usr/bin/env bash

#
# Test runner based on docker or podman, following the TYPO3 core runner (Build/Scripts/runTests.sh)
#
if [ "${CI}" != "true" ]; then
    trap 'echo "runTests.sh SIGINT signal emitted";cleanUp;exit 2' SIGINT
fi

printSummary() {
    cleanUp

    echo "" >&2
    echo "###########################################################################" >&2
    echo "Result of ${TEST_SUITE}" >&2
    echo "Container runtime: ${CONTAINER_BIN}" >&2
    echo "Container suffix: ${SUFFIX}"
    echo "PHP: ${PHP_VERSION}" >&2
    if [[ ${TEST_SUITE} =~ ^(composerUpdate|composerUpdateMin)$ ]]; then
        echo "TYPO3: ${CORE_VERSION}" >&2
        echo "PHPUnit: ${PHPUNIT_VERSION}" >&2
    fi
    if [[ ${SUITE_EXIT_CODE} -eq 0 ]]; then
        echo "SUCCESS" >&2
    else
        echo "FAILURE" >&2
    fi
    echo "###########################################################################" >&2
    echo "" >&2
    exit ${SUITE_EXIT_CODE}
}

cleanUp() {
    echo "Remove container for network \"${NETWORK}\""
    ATTACHED_CONTAINERS=$(${CONTAINER_BIN} ps --filter network=${NETWORK} --format='{{.Names}}')
    for ATTACHED_CONTAINER in ${ATTACHED_CONTAINERS}; do
        ${CONTAINER_BIN} kill ${ATTACHED_CONTAINER} >/dev/null
    done
    if [ ${CONTAINER_BIN} = "docker" ]; then
        ${CONTAINER_BIN} network rm ${NETWORK} >/dev/null
    else
        ${CONTAINER_BIN} network rm -f ${NETWORK} >/dev/null
    fi
}

getPhpImageVersion() {
    case ${1} in
        8.2)
            echo -n "1.15"
            ;;
        8.3)
            echo -n "1.16"
            ;;
        8.4)
            echo -n "1.8"
            ;;
        8.5)
            echo -n "1.8"
            ;;
    esac
}

getCoreConstraint() {
    case ${1} in
        14)
            echo -n "^14.3"
            ;;
        15)
            echo -n "^15.0"
            ;;
    esac
}

loadHelp() {
    # Load help text into $HELP
    read -r -d '' HELP <<EOF
Test runner of the TYPO3 PHPUnit extension. Executes unit tests, functional tests and code quality
suites in a container based test environment.

Usage: $0 [options] [file]

Options:
    -s <...>
        Specifies the test suite to run
            - cgl: test and fix all php files
            - checkBom: check UTF-8 files do not contain BOM
            - clean: clean up build, cache and testing related files and folders
            - composer: "composer" with all remaining arguments dispatched
            - composerInstall: "composer install"
            - composerInstallMax: "composer update", with no platform.php config
            - composerInstallMin: "composer update --prefer-lowest", with platform.php set to PHP version x.x.0
            - composerUpdate: "composer update" for the TYPO3 major selected with -t and the PHPUnit major selected with -U
            - composerUpdateMin: like composerUpdate, with the lowest releases ("--prefer-lowest")
            - composerValidate: "composer validate"
            - functional: PHP functional tests on SQLite
            - lintPhp: PHP linting
            - phpstan: phpstan analyze
            - unit (default): PHP unit tests
            - unitRandom: PHP unit tests in random order, add -o <number> to use specific seed

    -b <docker|podman>
        Container environment:
            - podman (default)
            - docker

    -p <8.2|8.3|8.4|8.5>
        Specifies the PHP minor version to be used
            - 8.2 (default): use PHP 8.2
            - 8.3: use PHP 8.3
            - 8.4: use PHP 8.4
            - 8.5: use PHP 8.5, required by TYPO3 15

    -t <14|15>
        Only with -s composerUpdate|composerUpdateMin
        Specifies the TYPO3 core major to install
            - 14 (default): TYPO3 14.3
            - 15: TYPO3 15, the development version of the core

    -U <11|12|13>
        Only with -s composerUpdate|composerUpdateMin
        Specifies the PHPUnit major to install
            - 11 (default): PHPUnit 11
            - 12: PHPUnit 12, PHP 8.3 and above
            - 13: PHPUnit 13, PHP 8.4 and above

    -x
        Only with -s unit|unitRandom|functional
        Send information to host instance for test or system under test break points. This is especially
        useful if a local PhpStorm instance is listening on default xdebug port 9003. A different port
        can be selected with -y

    -y <port>
        Send xdebug information to a different port than default 9003 if an IDE like PhpStorm
        is not listening on default port.

    -o <number>
        Only with -s unitRandom
        Set specific random seed to replay a random run in this order again. The phpunit randomizer
        outputs the used seed at the end (in gitlab core testing logs, too). Use that number to
        replay the unit tests in that order.

    -n
        Only with -s cgl
        Activate dry-run in CGL check that does not actively change files and only prints broken ones.

    -u
        Update existing ghcr.io/typo3/core-testing-*:latest container images and remove dangling local volumes.
        New images are published once in a while and only the latest ones are supported by CI. If
        the image versions in getPhpImageVersion() are changed, those images are pulled implicitly.

    -h
        Show this help.

Examples:
    # Run unit and functional tests using PHP 8.2 against TYPO3 14 and PHPUnit 11
    ./Build/Scripts/runTests.sh -s composerUpdate
    ./Build/Scripts/runTests.sh -s unit
    ./Build/Scripts/runTests.sh -s functional

    # Run the functional tests using PHP 8.5 against TYPO3 15 and PHPUnit 13
    ./Build/Scripts/runTests.sh -p 8.5 -t 15 -U 13 -s composerUpdate
    ./Build/Scripts/runTests.sh -p 8.5 -s functional

    # Run a single test file
    ./Build/Scripts/runTests.sh -s functional tests/Functional/ExtensionTest.php
EOF
}

# Test if docker exists, else exit out with error
if ! type "docker" >/dev/null 2>&1 && ! type "podman" >/dev/null 2>&1; then
    echo "This script relies on docker or podman. Please install" >&2
    exit 1
fi

# Go to the directory this script is located, so everything else is relative
# to this dir, no matter from where this script is called, then go up two dirs.
THIS_SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" >/dev/null && pwd)"
cd "$THIS_SCRIPT_DIR" || exit 1
cd ../../ || exit 1
ROOT_DIR="${PWD}"

# Default variables
TEST_SUITE="unit"
PHP_VERSION="8.2"
CORE_VERSION="14"
PHPUNIT_VERSION="11"
PHP_XDEBUG_ON=0
PHP_XDEBUG_PORT=9003
CGLCHECK_DRY_RUN=""
PHPUNIT_RANDOM=""
CONTAINER_BIN=""
CONTAINER_INTERACTIVE="-it --init"
HOST_UID=$(id -u)
HOST_PID=$(id -g)
USERSET=""
CI_PARAMS="${CI_PARAMS:-}"
SUFFIX=$(echo $RANDOM)
NETWORK="typo3-deprecations-${SUFFIX}"
CONTAINER_HOST="host.docker.internal"

# Option parsing updates above default vars
# Reset in case getopts has been used previously in the shell
OPTIND=1
# Array for invalid options
INVALID_OPTIONS=()
# Simple option parsing based on getopts (! not getopt)
while getopts ":b:s:p:t:U:o:xy:nhu" OPT; do
    case ${OPT} in
        s)
            TEST_SUITE=${OPTARG}
            ;;
        b)
            if ! [[ ${OPTARG} =~ ^(docker|podman)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            CONTAINER_BIN=${OPTARG}
            ;;
        p)
            PHP_VERSION=${OPTARG}
            if ! [[ ${PHP_VERSION} =~ ^(8.2|8.3|8.4|8.5)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            ;;
        t)
            CORE_VERSION=${OPTARG}
            if ! [[ ${CORE_VERSION} =~ ^(14|15)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            ;;
        U)
            PHPUNIT_VERSION=${OPTARG}
            if ! [[ ${PHPUNIT_VERSION} =~ ^(11|12|13)$ ]]; then
                INVALID_OPTIONS+=("${OPTARG}")
            fi
            ;;
        o)
            PHPUNIT_RANDOM="--random-order-seed=${OPTARG}"
            ;;
        x)
            PHP_XDEBUG_ON=1
            ;;
        y)
            PHP_XDEBUG_PORT=${OPTARG}
            ;;
        n)
            CGLCHECK_DRY_RUN="-n"
            ;;
        h)
            loadHelp
            echo "${HELP}"
            exit 0
            ;;
        u)
            TEST_SUITE=update
            ;;
        \?)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
        :)
            INVALID_OPTIONS+=("${OPTARG}")
            ;;
    esac
done

# Exit on invalid options
if [ ${#INVALID_OPTIONS[@]} -ne 0 ]; then
    echo "Invalid option(s):" >&2
    for I in "${INVALID_OPTIONS[@]}"; do
        echo "-"${I} >&2
    done
    echo >&2
    echo "Use \"Build/Scripts/runTests.sh -h\" to display help and valid options" >&2
    exit 1
fi

if [ "${CI}" == "true" ]; then
    CONTAINER_INTERACTIVE=""
elif [ ! -t 0 ] || [ ! -t 1 ]; then
    # If stdin or stdout is not a TTY (e.g. a script runner, pipe, or non-interactive shell),
    # drop the interactive "-it" flags automatically to avoid podman warning "The input device
    # is not a TTY." and docker failure, and to keep redirected output free of TTY control characters.
    # Keep "--init" so the PID 1 init process still forwards signals (e.g. ctrl-c) to the test process.
    CONTAINER_INTERACTIVE="--init"
fi

# determine default container binary to use: 1. podman 2. docker
if [[ -z "${CONTAINER_BIN}" ]]; then
    if type "podman" >/dev/null 2>&1; then
        CONTAINER_BIN="podman"
    elif type "docker" >/dev/null 2>&1; then
        CONTAINER_BIN="docker"
    fi
fi

if [ "$(uname)" != "Darwin" ] && [ "${CONTAINER_BIN}" = "docker" ]; then
    # Run docker jobs as current user to prevent permission issues. Not needed with podman.
    USERSET="--user $HOST_UID"
fi

if ! type ${CONTAINER_BIN} >/dev/null 2>&1; then
    echo "Selected container environment \"${CONTAINER_BIN}\" not found. Please install or use -b option to select one." >&2
    exit 1
fi

IMAGE_PHP="ghcr.io/typo3/core-testing-$(echo "php${PHP_VERSION}" | sed -e 's/\.//'):$(getPhpImageVersion $PHP_VERSION)"

# Remove handled options and leaving the rest in the line, so it can be passed raw to commands
shift $((OPTIND - 1))

# Create .cache dir: composer needs this.
mkdir -p .cache

${CONTAINER_BIN} network create ${NETWORK} >/dev/null

if [ ${CONTAINER_BIN} = "docker" ]; then
    # docker needs the add-host for xdebug remote debugging. podman has host.container.internal built in
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} --rm --network ${NETWORK} --add-host "${CONTAINER_HOST}:host-gateway" ${USERSET} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    TMPFS_MOUNT_OPTIONS="rw,noexec,nosuid,uid=${HOST_UID},gid=${HOST_PID}"
else
    # podman
    CONTAINER_HOST="host.containers.internal"
    CONTAINER_COMMON_PARAMS="${CONTAINER_INTERACTIVE} ${CI_PARAMS} --rm --network ${NETWORK} -v ${ROOT_DIR}:${ROOT_DIR} -w ${ROOT_DIR}"
    TMPFS_MOUNT_OPTIONS="rw,noexec,nosuid"
fi

if [ ${PHP_XDEBUG_ON} -eq 0 ]; then
    XDEBUG_MODE="-e XDEBUG_MODE=off"
    XDEBUG_CONFIG=" "
else
    XDEBUG_MODE="-e XDEBUG_MODE=debug -e XDEBUG_TRIGGER=foo"
    XDEBUG_CONFIG="client_port=${PHP_XDEBUG_PORT} client_host=${CONTAINER_HOST}"
fi

# Suite execution
case ${TEST_SUITE} in
    cgl)
        # Active dry-run for cgl needs not "-n" but specific options
        if [ -n "${CGLCHECK_DRY_RUN}" ]; then
            CGLCHECK_DRY_RUN="--dry-run --diff"
        fi
        COMMAND="php -dxdebug.mode=off .Build/bin/php-cs-fixer fix -v ${CGLCHECK_DRY_RUN} --config=Build/php-cs-fixer/config.php"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name cgl-${SUFFIX} ${IMAGE_PHP} ${COMMAND}
        SUITE_EXIT_CODE=$?
        ;;
    checkBom)
        COMMAND="! grep -rlI \$'\\xEF\\xBB\\xBF' --exclude-dir=.git --exclude-dir=.cache --exclude-dir=.Build ."
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name check-utf8bom-${SUFFIX} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    clean)
        rm -rf .Build/ .cache/ composer.lock .php-cs-fixer.cache
        SUITE_EXIT_CODE=0
        ;;
    composer)
        COMMAND=(composer "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerInstall)
        COMMAND=(composer install --no-progress --no-interaction "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-install-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerInstallMax)
        COMMAND="composer config --unset platform.php; composer update --no-progress --no-interaction; composer dumpautoload"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-install-max-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    composerInstallMin)
        COMMAND="composer config platform.php ${PHP_VERSION}.0; composer update --prefer-lowest --no-progress --no-interaction; composer config --unset platform.php; composer dumpautoload"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-install-min-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    composerUpdate)
        # The vendor tree must go, not just the lock file: Composer boots plugins from it before resolving, so a
        # leftover tree of another core major runs that core's "typo3/class-alias-loader" over the new one.
        # "--with" narrows the constraints for this resolution only, composer.json keeps the spanning ones.
        rm -rf .Build/bin/ .Build/vendor/ composer.lock
        COMMAND=(composer update --no-progress --no-interaction --with-all-dependencies --with "typo3/cms-core:$(getCoreConstraint ${CORE_VERSION})" --with "phpunit/phpunit:^${PHPUNIT_VERSION}")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-update-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerUpdateMin)
        rm -rf .Build/bin/ .Build/vendor/ composer.lock
        COMMAND=(composer update --prefer-lowest --no-progress --no-interaction --with-all-dependencies --with "typo3/cms-core:$(getCoreConstraint ${CORE_VERSION})" --with "phpunit/phpunit:^${PHPUNIT_VERSION}")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-update-min-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    composerValidate)
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name composer-validate-${SUFFIX} -e COMPOSER_CACHE_DIR=.cache/composer ${IMAGE_PHP} composer validate --strict --no-check-lock
        SUITE_EXIT_CODE=$?
        ;;
    functional)
        # The SQLite databases live on a tmpfs mount, which avoids permission issues and is fast.
        mkdir -p "${ROOT_DIR}/.Build/Web/typo3temp/var/tests/functional-sqlite-dbs/"
        CONTAINERPARAMS="-e typo3DatabaseDriver=pdo_sqlite --tmpfs ${ROOT_DIR}/.Build/Web/typo3temp/var/tests/functional-sqlite-dbs/:${TMPFS_MOUNT_OPTIONS}"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name functional-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${CONTAINERPARAMS} ${IMAGE_PHP} .Build/bin/phpunit -c Build/phpunit/FunctionalTests.xml "$@"
        SUITE_EXIT_CODE=$?
        ;;
    lintPhp)
        COMMAND="php -v | grep '^PHP'; find src/ tests/ Build/ -name \\*.php -print0 | xargs -0 -n1 -P"'$(nproc 2>/dev/null || echo 4)'" php -dxdebug.mode=off -l >/dev/null"
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name lint-php-${SUFFIX} ${IMAGE_PHP} /bin/sh -c "${COMMAND}"
        SUITE_EXIT_CODE=$?
        ;;
    phpstan)
        COMMAND=(php -dxdebug.mode=off .Build/bin/phpstan analyse -c Build/phpstan/phpstan.neon --verbose --no-interaction --memory-limit 4G "$@")
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name phpstan-${SUFFIX} ${IMAGE_PHP} "${COMMAND[@]}"
        SUITE_EXIT_CODE=$?
        ;;
    unit)
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name unit-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${IMAGE_PHP} .Build/bin/phpunit -c Build/phpunit/UnitTests.xml "$@"
        SUITE_EXIT_CODE=$?
        ;;
    unitRandom)
        ${CONTAINER_BIN} run ${CONTAINER_COMMON_PARAMS} --name unit-random-${SUFFIX} ${XDEBUG_MODE} -e XDEBUG_CONFIG="${XDEBUG_CONFIG}" ${IMAGE_PHP} .Build/bin/phpunit -c Build/phpunit/UnitTests.xml --order-by=random ${PHPUNIT_RANDOM} "$@"
        SUITE_EXIT_CODE=$?
        ;;
    update)
        # pull typo3/core-testing-* versions of those ones that exist locally
        echo "> pull ghcr.io/typo3/core-testing-* versions of those ones that exist locally"
        ${CONTAINER_BIN} images "ghcr.io/typo3/core-testing-*" --format "{{.Repository}}:{{.Tag}}" | xargs -I {} ${CONTAINER_BIN} pull {}
        echo ""
        # remove "dangling" typo3/core-testing-* images (those tagged as <none>)
        echo "> remove \"dangling\" ghcr.io/typo3/core-testing-* images (those tagged as <none>)"
        ${CONTAINER_BIN} images --filter "reference=ghcr.io/typo3/core-testing-*" --filter "dangling=true" --format "{{.ID}}" | xargs -I {} ${CONTAINER_BIN} rmi -f {}
        echo ""
        SUITE_EXIT_CODE=0
        ;;
    *)
        loadHelp
        echo "Invalid -s option argument ${TEST_SUITE}" >&2
        echo >&2
        echo "${HELP}" >&2
        exit 1
        ;;
esac

# Cleanup, print summary && exit with exitcode
printSummary
