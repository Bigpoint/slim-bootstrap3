<?php
namespace SlimBootstrap\Endpoint;

use \SlimBootstrap;

class Info implements SlimBootstrap\Endpoint\Get
{
    public function setClientId(string $clientId)
    {
        // nothing to do
    }

    public function get(array $routeArguments, array $queryParameters, array $data): array
    {
        $gitVersion = \trim(
            \shell_exec('git describe --tags --exact-match || git symbolic-ref -q --short HEAD')
        );

        return [
            'repoUrl'  => $this->getRepoUrl($gitVersion),
            'version'  => $gitVersion,
            'packages' => $this->readComposerPackageVersions(
                __DIR__ . '/../../../../../../composer.lock'
            ),
        ];
    }

    /**
     * Reads composer.lock and returns an array with used packages and its version.
     * When a git packages version contains dev, first characters of
     * reference will be added to version.
     *
     * @param string $composerLockPath
     *
     * @return array
     */
    private function readComposerPackageVersions(string $composerLockPath) : array
    {
        $result = [];
        $data   = \json_decode(\file_get_contents($composerLockPath), true);

        if (false === \is_array($data) || false === \array_key_exists('packages', $data)) {
            return [];
        }

        foreach ($data['packages'] as $package) {
            $versionString = $package['version'];

            if (true === \array_key_exists('source', $package)
                && $package['source']['type'] === 'git'
                && false !== \strpos($package['version'], 'dev')
            ) {
                $versionString .= ' (' . \substr($package['source']['reference'], 0, 6) .'...)';
            }

            $packageUrl = 'https://packagist.org/';

            if (false !== \strpos($package['notification-url'], 'packagist.bigpoint.net')) {
                $packageUrl = 'https://packagist.bigpoint.net/';
            }

            $packageUrl .= 'packages/' . $package['name'] . '#' . $package['version'];

            $result[$package['name']] = [
                'version'       => $package['version'],
                'versionString' => $versionString,
                'packageUrl'    => $packageUrl,
            ];
        }

        return $result;
    }

    /**
     * @param string $gitVersion
     *
     * @return string
     */
    private function getRepoUrl($gitVersion)
    {
        $gitUrl = \trim(\shell_exec('git config --get remote.origin.url'));

        if ('' !== $gitUrl) {
            $repoUrl = \explode('@', $gitUrl);

            return 'https://' . \str_replace(
                [
                    ':',
                    '.git',
                ],
                [
                    '/',
                    '/tree/' . $gitVersion,
                ],
                $repoUrl[1]
            );
        }

        return '';
    }
}
