## SteemPress - Blockchain Powered Blog

The goal of SteemPress is to provide a standalone blog engine based on the [steem blockchain](https://github.com/steemit/steem).

### Features

- Twig Templating
- Localization Support

### Running steempress via Docker

From the steempress folder, there are two primary commands:

**Initialize the container:**

```
docker build --tag steempress .
```

**Start the container:**

```
docker run -v $(pwd):/src/steempress -p 80:80 steempress
```

### Future Feature Ideas

- Comments
- Voting
- Authoring Tools
- Fullpage caching

### Try it Out on Heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)
