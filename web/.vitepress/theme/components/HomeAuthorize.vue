<template>
  <div class="home">
    <div class="container">
      <h2>授权</h2>
      <h3>类库</h3>
      <p>
        <a href="https://github.com/yansongda/pay">yansongda/pay</a> 项目采用 MIT 协议开源发布，任何人均可在遵循 MIT 协议的情况下随意使用。
      </p>
      <h3>文档</h3>
      <p>
        网站 <a href="https://pay.yansongda.cn">pay.yansongda.cn</a>（包括但不限于 `pay.yansongda.cn` 域名下所有文字、图片）以 <a href="https://creativecommons.org/licenses/by/4.0/deed.zh">CC BY SA 4.0 国际许可协议</a> 授权。
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: "HomeAuthorize"
}
</script>

<style lang="scss" scoped>
@import '../css/home';

.home {
  margin-top: 50px;

  .container {
    p {
      display: block;
      margin-block-start: 1em;
      margin-block-end: 1em;
      margin-inline-start: 0;
      margin-inline-end: 0;
    }

    a {
      font-weight: 500;
      color: #3eaf7c;
      text-decoration: none;
      overflow-wrap: break-word;
    }

    h1, h2, h3, h4, h5, h6 {
      font-weight: 600;
      line-height: 1.25;
    }

    h2 {
      font-size: 1.65rem;
      padding-bottom: .3rem;
      border-bottom: 1px solid #eaecef;
    }

    h3 {
      font-size: 1.35rem;
      display: block;
      margin-block-start: 1em;
      margin-block-end: 1em;
      margin-inline-start: 0;
      margin-inline-end: 0;
    }
  }
}
</style>
