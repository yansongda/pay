<template>
  <div class="home">
    <div class="container">
      <img src="/images/logo.png" alt="pay logo">
      <p class="action">
        <a href="/docs/v3/" class="action-button primary">快速上手-></a>
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "HomePrimary"
}
</script>

<style lang="scss" scoped>
@media (min-width: 640px) {
  .home {
    padding: 0 48px;
  }
}
@media (min-width: 960px) {
  .home {
    padding: 0 64px;
  }
}

.home {
  margin-bottom: 50px;
  padding: 0 24px;
}

.container {
  max-width: 1152px;
  text-align: center;
  margin: 0 auto;

  img {
    display: inline-block;
  }

  .action .action-button {
    display: inline-block;
    font-size: 1.2rem;
    padding: .8rem 1.6rem;
    border-width: 2px;
    border-style: solid;
    border-radius: 4px;
    transition: background-color var(--t-color);
    box-sizing: border-box;

    &.primary {
      color: #fff;
      background-color: #3eaf7c;
      border-color: #3eaf7c;
    }
  }
}
</style>
