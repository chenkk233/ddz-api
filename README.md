<div align="center">
<img src="https://www.otscp.com/upload/74d80a150cb2cdbceaf9392176c5c8b.jpg" alt="icon" width="50px"/>
<h1 align="center">PHP-DDZ-API</h1>


一键部署斗地主后台数据API，支持使用以下开发语言或游戏引擎开发前端界面

**HTML/JavaScript** / C# / C++ / Java /Node.js/ Vue

Unity/UE4/UE5/Gamemaker/Cocos

只要你的前端语言或游戏引擎支持Http协议的接口协议，就可以连接本项目的API接口完成数据交互。

[快速开始](#如何使用) / [API文档](https://chatanywhere.apifox.cn/) / [项目详细文字教程](https://file.otscp.com/web/#/684688848/0)

[我的个人博客](https://www.otscp.com)

</div>

## 隐私声明

该项目的API接口，属于离线部署，用户在下载后，将自己部署MySQL数据库存储玩家的数据。一切数据流动将在本地运转，作者本人不对数据安全性负责。

## 更新日志

- **2023年11月09日** 首次发布项目1.0.0。

## 特点

1. 使用PHP开发，更易于理解和快速部署。
2. 前后端完全分离，用户可以选择自己熟练的编程语言来开发游戏界面。
3. 登录和接口交互使用了双token验证，保证了数据不会错乱。
4. 丰富完整的API接口文档，方便开发者进行二次开发。
5. 个人完全免费使用。

## 在线体验

**为了示例，我做一个在线体验的斗地主游戏，可以访问下列地址。**

- [雀魂斗地主在线网址](http://game.otscp.com)
  体验账号1：123@qq.com   密码：123
  体验账号2：456@qq.com   密码：123
  体验账号3：789@qq.com   密码：123
  由于目前并未开放注册，请暂时使用以上账号游玩，但由于斗地主是一个三人游戏，我还未开发机器人AI，所以请打开3种不同的浏览器访问。
  由于我的前端能力不是很好，所以使用了更为简单入门的html和javascript开发了这个游戏。

  我相信你们可以开发出更加完美的游戏，如果你成功开发出了一个使用本项目作为后台驱动的斗地主游戏，请记得@我，我会十分愿意去下载试玩。



## 如何使用

- 这里只做简单说明，如果还是不太明白，可以访问[项目详细文字教程](https://file.otscp.com/web/#/684688848/0)

  1.下载本项目

  2.部署到PHP环境中

  3.运行  http://localhost/install.php  (请将localhost更换为你的网站域名)

  4.填写数据库账号、密码、设置一个新的数据库名，点击安装。

  5.安装完毕。

## 🚩注意事项

 ❗️**本项目使用单一入口机制，所以请务必将本项目放在你的网站根目录。如果是Apache，将不必更改代码，会自动执行项目根目录下的.htaccesswe文件，如果你是Apache且根目录并没有这个文件，可能会导致项目报错。**

 ❗️**如果是Nginx 则需要手动设置配置文件，在Nginx配置文件中添加以下代码：**

```
  if (!-e $request_filename) {
           rewrite ^/index.php(.*)$ /index.php?s=$1 last;
           rewrite ^(.*)$ /index.php?s=$1 last;
           break;
        }
```

 ❗️***本项目仅可用于个人非商业用途，教育，非营利性科研工作中。严禁商用。***

  

  