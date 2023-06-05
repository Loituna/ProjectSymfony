<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Repository\UserRepository;

class UserAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private $userRepository;


    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $pseudo = $request->request->get('pseudo', '');

        $request->getSession()->set(Security::LAST_USERNAME, $pseudo);

        // Récupérer l'utilisateur en utilisant le pseudo
        $user = $this->userRepository->findOneBy(['pseudo' => $pseudo]);

        // Vérifier si l'utilisateur existe et est actif
        if (!$user || !$user->isActif()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé.');
        }

        return new Passport(
            new UserBadge($pseudo),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate('index'));

    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
