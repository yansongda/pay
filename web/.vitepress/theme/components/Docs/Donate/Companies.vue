<script setup lang="ts">
  interface Company {
    name: string
    image: string
    link: string
  }

  const companies: Company[] = [
    {
      name: 'jetbrains',
      link: 'https://www.jetbrains.com/',
      image: '/images/companies/jetbrains.png'
    },
  ]
</script>

<template>
  <div class="companies">
    <a v-for="company,idx in companies" :key="idx" :href="company.link" target="_blank" rel="noopener noreferrer" class="company">
      <img :src="company.image" :alt="company.name" class="company-img"/>
    </a>
  </div>
</template>

<style scoped lang="scss">
.companies {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-column-gap: 4px;

  @media (max-width: 720px) {
    grid-template-columns: repeat(2, 1fr);
    grid-column-gap: 0;
  }

  @media (max-width: 480px) {
    grid-template-columns: repeat(1, 1fr);
  }

  .company {
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
</style>
