# Changelog

## 2.0.3
- added missing jwt token time validations. This is only an issue since version `2.0.2` as the usage of Lcobucci\JWT was changed there.

## 2.0.2
- adjusted JWT auth to avoid deprecated warnings with Lcobucci\JWT >3.4

## 2.0.1
- understand "Bearer" and "bearer" in `Authorization` header for JWT authentications.

## 2.0.0
- renamed `\SlimBootstrap\Authentication.php` to `\SlimBootstrap\AuthenticationInterface.php` to avoid autoloader issues. **If you have custom authentication classes you need to change the interface it implements**

## 1.1.0
- add logging to see which client is authenticating over which authentication method

## 1.0.0
- initial version
- see [README.md](README.md) for instructions how to use it
