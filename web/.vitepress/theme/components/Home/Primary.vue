<script setup lang="ts">
</script>

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

<style scoped lang="scss">
@use '../../css/home';

.home {
  .container {
    margin-bottom: 50px;
    text-align: center;

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
      box-sizing: border-box;

      &.primary {
        color: #fff;
        background-color: #3eaf7c;
        border-color: #3eaf7c;
      }
    }
  }
}
</style>
