# Composer

## Setup with private repository

1) Go to [https://github.com/settings/tokens](https://github.com/settings/tokens) and generate a new token.

2) Your `composer.json`:

```json
{
    "require": {
        "instagram-private/instagram": "dev-master"
    },
 
    "repositories" : [
        {
            "type": "vcs",
            "url" : "git@github.com:YOUR_USERNAME/YOUR_REPO.git"
        }
    ],
    "config": {
      "github-oauth": {
        "github.com": "YOUR_GITHUB_TOKEN"
      }
    }
}
```

3) Now you can use composer with your private repository.