<?php

namespace Saxid\SimplesamlphpBundle\Security;

use Saxid\SimplesamlphpBundle\Security\Core\Authentication\Token\SamlToken;
use Saxid\SimplesamlphpBundle\Security\Core\User\SamlUserInterface;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class SamlAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $samlauth;
    protected $session;

    public function __construct($samlauth, Session $session)
    {
        $this->samlauth = $samlauth;
        $this->session = $session;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$this->samlauth->isAuthenticated()) {
            $this->session->clear();
        }

        $this->samlauth->requireAuth();
        $attributes = $this->samlauth->getAttributes();

        // eppn SAML 1 attribute name
        if(isset($attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0])) {
            $eppn = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'][0];
        }
        // eppn SAML 2 attribute name
        elseif(isset($attributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.6'][0])) {
            $eppn = $attributes['urn:oid:1.3.6.1.4.1.5923.1.1.1.6'][0];
        }
        else {
            throw new MissingOptionsException('No ePPN found');
        }

        $token = new SamlToken($eppn);
        $token->setAttributes($attributes);

        return $token;
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $username = $token->getUsername();
        $user = $userProvider->loadUserByUsername($username);

        if ($user instanceof SamlUserInterface) {
            $user->setSamlAttributes($token->getAttributes());
        }

        $authenticatedToken = new SamlToken($user, $user->getRoles());
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof SamlToken;
    }
}
