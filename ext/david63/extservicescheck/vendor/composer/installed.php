<?php return array(
    'root' => array(
        'name' => 'david63/extservicescheck',
        'pretty_version' => '2.2.0-RC1',
        'version' => '2.2.0.0-RC1',
        'reference' => null,
        'type' => 'phpbb-extension',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'david63/extservicescheck' => array(
            'pretty_version' => '2.2.0-RC1',
            'version' => '2.2.0.0-RC1',
            'reference' => null,
            'type' => 'phpbb-extension',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
