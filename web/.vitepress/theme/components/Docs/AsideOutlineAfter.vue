<script setup lang="ts">
  interface Company {
    name: string
    image: string
    link: string
  }

  const companies: Company[] = []
</script>

<template>
  <div class="business">
    <div class="tips">赞助</div>
    <div class="companies">
      <a v-for="company,idx in companies" :key="idx" :href="company.link" target="_blank" rel="noopener noreferrer" class="company">
        <img :src="company.image" :alt="company.name" class="company-img"/>
      </a>
    </div>
  </div>
</template>

<style scoped lang="scss">
.business {
  margin-top: 20px;

  .tips {
    font-size: 10px;
    text-align: center;
    color: #636363;
  }

  .companies {
    display: grid;
    grid-template-columns: repeat(1, 1fr);
    grid-column-gap: 4px;

    .company {
      width: 100%;
      height: 50px;
      margin: 2px 0;
      background-color: #f9f9f9;
      display: flex;
      justify-content: center;
      align-items: center;

      .company-img {
        max-height: 50px;
      }
    }
  }
}
</style>
