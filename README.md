# 脚本作用

自动打包云修复需要的widget.zip

# 使用步骤

- 确保cmd可运行php（下载个phpstudy之类的集成环境，添加php所在路径到环境变量即可）

- 复制widget.bat、widget.php到app项目的根目录的上级目录下

- 目录结构

```

	|- app          源码路径
	|- widget.bat   批处理文件
	|- widget.php
	|- widget.zip   运行widget.bat后自动生成，该包用于云修复上传

```

- 打开cmd命令行，运行widget.bat