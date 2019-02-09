import wx
import time
import socket
import json
import threading

HOST = 'socket.komputronika.com'  # Alamat socket server
PORT = 4444                       # Port server

class SocketThread (threading.Thread):
    def __init__(self, windows):
        threading.Thread.__init__(self)
        self.name = "ThreadSocket"
        self.windows = windows
   
    def run(self):
        self.update()

    def update(self):
        while True:  
            try:  
                data = Server.recv(1024)
                lines = data.splitlines()
            except:
                wx.MessageBox('Gagal membaca socket', 'Error', wx.OK | wx.ICON_ERROR)
                break    
            
            try:
                for i in range(0, len(lines)):
                    data = json.loads( lines[i].decode('UTF-8') )
                    wx.CallAfter(self.windows.text.SetLabel, 
                        "Suhu: "+unicode(data["data"]["suhu"])+
                        ", Kelembaban: "+unicode(data["data"]["kelembaban"])) 
            except:
                raise SystemExit

            time.sleep(0.500)

class MainForm(wx.Frame):
 
    def __init__(self):
        wx.Frame.__init__(self, None, wx.ID_ANY, "Demo Socket Server", size=(510,210))

        self.InitUI()
        self.thread1 = SocketThread(self)
        self.start = False

    def InitUI(self):
      
        panel = wx.Panel(self, wx.ID_ANY)
        sizer = wx.GridBagSizer(2, 1)

        self.text = wx.StaticText(panel, label="", size=(400, -1))
        self.text.SetForegroundColour('RED')
        
        font = wx.Font(pointSize=12, family=wx.FONTFAMILY_DEFAULT, style=wx.NORMAL, weight=wx.FONTWEIGHT_BOLD)
        self.text.SetFont( font )

        sizer.Add(self.text, pos=(0, 0), flag=wx.TOP|wx.LEFT|wx.BOTTOM|wx.RIGHT, border=50)

        self.toggleBtn = wx.Button(panel, wx.ID_ANY, "Mulai")
        self.toggleBtn.Bind(wx.EVT_BUTTON, self.OnToggle)
        sizer.Add(self.toggleBtn, pos=(1, 0), 
            flag=wx.EXPAND|wx.LEFT|wx.RIGHT, border=50)

        panel.SetSizerAndFit(sizer)   
 
    def OnToggle(self, event):  
        if self.start==False:
            self.thread1.start()      
            self.start=True

    def OnClose(self, event):
        Server.Close();
        self.Destroy()  
 
# Program utama
if __name__ == "__main__":
    try:
        Server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        Server.connect((HOST, PORT))
        Server.sendall('{"action":"sub","topic":"demo"}\n'.encode('UTF-8'))
    except:
        print "Tidak dapat terhubung dengan server: "+HOST+":"+PORT
        raise SystemExit

    App = wx.App()
    MainForm().Show()
    MainForm().Centre()
    App.MainLoop()
