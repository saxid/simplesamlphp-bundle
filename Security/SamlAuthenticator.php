<?php

namespace Saxid\SimplesamlphpBundle\Security;

use Saxid\SimplesamlphpBundle\Security\Core\Authentication\Token\SamlToken;
use Saxid\SimplesamlphpBundle\Security\Core\User\SamlUserInterface;
use Saxid\SimplesamlphpBundle\Exception\MissingSamlAuthAttributeException;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SamlAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $samlAuth;
    protected $session;

    public function __construct($samlAuth, Session $session, $authAttribute)
    {
        $this->samlAuth = $samlAuth;
        $this->session = $session;
        $this->authAttribute = $authAttribute;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$this->samlAuth->isAuthenticated()) {
            $this->session->clear();
        }

        $this->samlAuth->requireAuth();
        $attributes = $this->samlAuth->getAttributes();

        foreach ($this->authAttribute as $val){

          if (array_key_exists($val, $attributes)) {
              $eppn = $attributes[$val][0];
              break;
          } else {
            throw new MissingSamlAuthAttributeException(
                sprintf("Attribute '%s' was not found in SAMLResponse", $val)
            );
          }

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
