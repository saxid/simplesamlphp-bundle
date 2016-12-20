<?php

namespace Saxid\SimplesamlphpBundle\Twig;

class LogoutLinkExtension extends \Twig_Extension
{
    private $auth;

    public function __construct(\SimpleSAML_Auth_Simple $auth) {
        $this->auth = $auth;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            'getLogoutURL' => new \Twig_Function_Method($this, 'getLogoutURL')
        );
    }

    /**
     * @param string $string
     * @return int
     */
    public function getLogoutURL() {
        return $this->auth->getLogoutURL();
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'simplesamlphp_logout_link_extension';
    }
}
