<script setup lang="ts">
  const companies = [
    {
      name: 'jetbrains',
      link: 'https://www.jetbrains.com/',
      image: '/images/companies/jetbrains.png'
    },
    {
      name: 'df81.com',
      link: 'https://df81.com/',
      image: '/images/companies/df81.png'
    },
  ]
</script>

<template>
  <div class="donate">
    <div class="companies">
      <a v-for="company,idx in companies" :key="idx" :href="company.link" target="_blank" rel="noopener noreferrer" class="company">
        <img :src="company.image" :alt="company.name" class="company-img"/>
      </a>
    </div>
  </div>
</template>

<style scoped lang="scss">
.donate {
  width: 100%;

  .companies {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    column-gap: 4px;

    .company {
      width: 100%;
      height: 100px;
      margin: 2px 0;
      background-color: #f9f9f9;
      display: flex;
      justify-content: center;
      align-items: center;

      .company-img {
        max-height: 100px;
      }
    }
  }
}
</style>