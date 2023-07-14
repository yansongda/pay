<script setup>

</script>

<template>
  <div class="flex flex-col items-center justify-center">
    <div class="flex flex-wrap justify-center items-center">
      <a href="https://www.jetbrains.com/?from=VitePress" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center">
        <img src="/images/companies/jetbrains.svg" alt="JetBrains" class="h-12 w-12" />
      </a>
    </div>
  </div>
</template>

<style scoped lang="scss">
.sponsor-container {
  --max-width: 100%;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(var(--max-width), 1fr));
  column-gap: 4px;
}

.sponsor-container.platinum {
  --max-width: 240px;
}
.sponsor-container.gold {
  --max-width: 180px;
}
.sponsor-container.silver {
  --max-width: 140px;
}

.sponsor-item {
  margin: 2px 0;
  background-color: var(--vt-c-white-soft);
  display: flex;
  justify-content: space-around;
  align-items: center;
  border-radius: 2px;
  transition: background-color 0.2s ease;
  height: calc(var(--max-width) / 2 - 6px);
}
.sponsor-item.action {
  font-size: 11px;
  color: var(--vt-c-text-3);
}
.sponsor-item img {
  max-width: calc(var(--max-width) - 30px);
  max-height: calc(var(--max-width) / 2 - 20px);
}
.special .sponsor-item {
  height: 160px;
}
.special .sponsor-item img {
  max-width: 300px;
  max-height: 150px;
}

/* dark mode */
.dark .aside .sponsor-item,
.dark .landing .sponsor-item {
  background-color: var(--vt-c-bg-soft);
}
.aside .sponsor-item img,
.landing .sponsor-item img {
  transition: filter 0.2s ease;
}
.dark .aside .sponsor-item img,
.dark .landing .sponsor-item img {
  filter: grayscale(1) invert(1);
}
.dark .aside .sponsor-item:hover,
.dark .landing .sponsor-item:hover {
  color: var(--vt-c-indigo);
  background-color: var(--vt-c-white-mute);
}
.dark .sponsor-item:hover img {
  filter: none;
}

/* aside mode (on content pages) */
.sponsor-container.platinum.aside {
  --max-width: 110px;
  column-gap: 1px;
}
.aside .sponsor-item {
  margin: 1px 0;
}
.aside .special .sponsor-item {
  width: 100%;
  height: 60px;
}
.aside .special .sponsor-item img {
  width: 120px;
}
.aside .platinum .sponsor-item {
  width: 111px;
  height: 50px;
}
.aside .platinum .sponsor-item img {
  max-width: 88px;
}

/* narrow, aside will be hidden under this state so it's mutually exclusive */
@media (max-width: 720px) {
  .sponsor-container.platinum {
    --max-width: 180px;
  }
  .sponsor-container.gold {
    --max-width: 140px;
  }
  .sponsor-container.silver {
    --max-width: 120px;
  }
}

@media (max-width: 480px) {
  .sponsor-container.platinum {
    --max-width: 150px;
  }
  .sponsor-container.gold {
    --max-width: 120px;
  }
  .sponsor-container.silver {
    --max-width: 100px;
  }
}
</style>