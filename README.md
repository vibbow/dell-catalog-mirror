# Dell Catalog 中国加速镜像服务器  

## 工作原理  
将所有对 catalog.xml / catalog.xml.gz 访问重定向到自定义 catalog.xml  
并修改 catalog.xml 里的固件文件下载地址修改为 Dell 的中国官方镜像 dl.dell.com

## 其他修改  
### 魔改1
Dell 官方的 catalog 服务器里有两个 catalog.xml.gz 文件，分别为  
/catalog.xml.gz  
/catalog/catalog.xml.gz  
其中前者已不再更新，后者是正常更新状态。  
因为无论 iDRAC 请求的是哪个路径的 catalog.xml.gz，均需要返回后者的文件。  

### 魔改2
部分版本的 iDRAC 会无视 catalog.xml.gz 里写的下载路径，而是会直接请求 catalog 服务器里的固件文件。因此也需要将这部分请求重定向回 Dell 的官方镜像。
