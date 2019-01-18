```
 _   _
| \ | | __ _ _ __   _____   __
|  \| |/ _` | '_ \ / _ \ \ / /
| |\  | (_| | | | | (_) \ V / 
|_| \_|\__,_|_| |_|\___/ \_/ 

A multi process manager for PHP 

Version v0.1.0
```
### 场景
php-cli模式下实现的master-worker多进程模型，该模式下，php常驻内存，能够实时地执行一些任务。

### 安装
```
composer create-project tree/nanov && source ~/.bashrc
```

### 使用
```
nanov start/stop/quit/reload
```

### 参数说明
- quit 优雅退出
- stop 停止中断
- start 启动
- reload 重启

### 实现的功能
- [x] 优化日志
- [x] 优雅地重启和退出
- [x] 工作进程的最大执行时间
- [x] 读取配置文件来配置系统



 


