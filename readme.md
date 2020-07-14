过Yii2 csrf验证
==============

csrf原理
-------
- 在表单中生成隐藏字段csrf_token，post请求时，验证csrf_token

csrf处理
-------
- 1.请求登录页面，预处理csrf及cookie
- 2.带上cookie和csrf字段post

方案
---
- 1.index.php 使用CURLOPT_COOKIEJAR
- 2.main.php  手工处理cookie