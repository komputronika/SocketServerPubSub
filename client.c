#include <stdio.h> 
#include <sys/socket.h> 
#include <stdlib.h> 
#include <netinet/in.h> 
#include <string.h> 

#define ADDR "127.0.0.1" 
#define PORT 4444 

int main(int argc, char const *argv[]) 
{ 
    struct sockaddr_in address; 

    int sock = 0, data; 
    char *json = "{\"action\":\"sub\",\"topic\":\"demo\"}\n{\"action\":\"pub\",\"topic\":\"demo\",\"data\":\"Test dengan C\"}"; 
    char buffer[2048] = {0}; 

    if ((sock = socket(AF_INET, SOCK_STREAM, 0)) < 0) 
    { 
        printf("Tidak berhasil membuka socket\n"); 
        return -1; 
    } 

    memset(&address, '0', sizeof(address)); 

    address.sin_family = AF_INET; 
    address.sin_addr.s_addr = inet_addr(ADDR);
    address.sin_port = htons(PORT); 
    
    /*if(inet_pton(AF_INET, "127.0.0.1", &address.sin_addr)<=0) 
    { 
        printf("IP address tidak didukung\n"); 
        return -1; 
    }*/ 

    if (connect(sock, (struct sockaddr *)&address, sizeof(address)) < 0) 
    { 
        printf("Koneksi ke socket server gagal\n"); 
        return -1; 
    } 

    send(sock , json , strlen(json) , 0 ); 
    printf("Mengirim data ke server:\n%s\n", json); 
    
    data = read( sock , buffer, 2048); 
    printf("Balasan dari server:\n",buffer ); 
    printf("%s\n",buffer ); 
    return 0; 
} 
