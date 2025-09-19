<script setup lang="ts">
  const people = [
    { nickname: '醒醒吧 / **平', intent: '￥10.00' },
    { nickname: 'Akun', intent: '提供微信支付测试资源帮助测试' },
    { nickname: '*坤', intent: '￥10.00' },
    { nickname: '*爆', intent: '￥66.00' },
    { nickname: '*洲', intent: '￥10.00' },
    { nickname: '*友', intent: '￥300.00' },
    { nickname: '一杯小蓝 / *彦', intent: '￥16.80' },
    { nickname: 'u*e', intent: '￥11.00' },
    { nickname: 'N*s', intent: '￥10.00' },
    { nickname: '*虫', intent: '￥100.00' },
    { nickname: '**波', intent: '￥10.00' },
    { nickname: '*※', intent: '￥8.88' },
    { nickname: '往**', intent: '￥19.80' },
    { nickname: '建*u', intent: '￥17.60' },
    { nickname: '*酱', intent: '￥20.00' },
    { nickname: '*※', intent: '￥10.00' },
    { nickname: 'N*s', intent: '￥10.00' },
    { nickname: 'FastAdmin开源社区 / F4nniu', intent: '￥100.00', link: 'https://www.fastadmin.net' },
    { nickname: '小黑 / **瑞', intent: '￥50.00' },
    { nickname: '《建站管家》多语言建站系统 / **明', intent: '￥108.88', link: 'https://df81.com/' },
    { nickname: 'N*s', intent: '￥10.00' },
    { nickname: '*洋', intent: '￥99.00' },
    { nickname: '建*u', intent: '￥88.80' },
  ]
</script>

<template>
  <div class="people">
    <a v-for="person,idx in people" :key="idx" :href="person.link ?? ''" target="_blank" rel="noopener noreferrer" class="person">
      <div class="person-nickname">{{ person.nickname }}</div>
      <div class="person-intent">{{ person.intent }}</div>
    </a>
  </div>
</template>

<style scoped lang="scss">
.people {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  grid-column-gap: 4px;

  @media (max-width: 720px) {
    grid-template-columns: repeat(3, 1fr);
  }

  @media (max-width: 480px) {
    grid-template-columns: repeat(2, 1fr);
  }

  .person {
    height: 100px;
    margin: 2px 0;
    background-color: #f9f9f9;
    display: flex;
    flex-flow: column nowrap;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    text-decoration: none;

    .person-nickname {
      flex: 2;
      color: #4cb1c5;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .person-intent {
      flex: 1;
      font-size: 10px;
      color: #b0b0b0;
      display: flex;
      justify-content: center;
      align-items: center;
      line-height: 15px;
      text-align: center;
    }
  }
}
</style>
