#!/usr/bin/python
# -*- coding: utf-8 -*-

import os,time,re,math,random, pickle, operator,sys,socket
import urllib, urllib2,httplib2,httplib

import logging
import ConfigParser

config = ConfigParser.RawConfigParser()
config.read('example.cfg')


path="/home/ubuntu/"
sys.path.append("/home/ubuntu/")

LOG_FILENAME = path + 'KingsAge16.log'
#logging.basicConfig(filename=LOG_FILENAME,level=logging.DEBUG)

# create logger
logger = logging.getLogger("log")
logger.setLevel(logging.DEBUG)
# create console handler and set level to debug
ch = logging.FileHandler(LOG_FILENAME)
ch.setLevel(logging.DEBUG)
# create formatter
formatter = logging.Formatter("%(asctime)s - %(name)s - %(levelname)s - %(message)s")
# add formatter to ch
ch.setFormatter(formatter)
# add ch to logger
logger.addHandler(ch)


h = httplib2.Http(".cache",timeout=10)


config = ConfigParser.RawConfigParser()
config.read('kings16.cfg')
usuario = config.get('Section1', 'usuario')
password = config.get('Section1', 'password')
server = config.get('Section1', 'server')
pueblo_fake = config.get('Section1', 'pueblo_fake')
SD_FRAMEWORK_SESSION = config.get('Section1', 'SD_FRAMEWORK_SESSION')
KingsAge_Game = config.get('Section1', 'KingsAge-Game')
game_hash = config.get('Section1', 'game_hash')
ally = config.get('Section1', 'ally')
directorio = config.get('Section1', 'directorio')

print usuario
print password
print server
print pueblo_fake
print SD_FRAMEWORK_SESSION
print KingsAge_Game
print game_hash
print ally
print directorio

units=[20,22,18,10,11,30,35]


def remove_not_exist():
    global lista
    global lista1

    ######################## Elimina una entrada en la lista que ya no esta como ataque
    if len(lista1)<=0:
       logger.debug('Lista 1 VACIA')
       return
    if len(lista)==0:
       logger.debug('Lista 0 VACIA')

    #lista  = es la lista principal
    #lista1 = es la lista leida de la bandera


    logger.debug('Inicio Subrutina BORRADO  ____lista1= ' + str(len(lista1))+ '    Lista=  ' + str(len(lista)) )

    delete = 0
    x = len(lista)-1
    y = 0
    while x >= 0:
        esta = False
        y = len(lista1)-1
        while y >= 0 and esta == False:
            diff = abs(lista1[y][11] - lista[x][11])
            if  lista[x][2].encode("utf-8") == lista1[y][2].encode("utf-8") and lista[x][3].encode("utf-8") == lista1[y][3].encode("utf-8") and lista[x][6].encode("utf-8") == lista1[y][6].encode("utf-8") and (diff <= 1):
                lista1.pop(y)
                x = x - 1
                esta = True
                break
            y = y - 1
        if y == -1 and esta == False:
            print "Borrando..", str(lista[x])
            logger.debug('Borrando_0 ' + str(lista[x]))
            lista.pop(x)
            delete = delete + 1
            x = x - 1



    ######################### A�ADIR LOS QUE NO ESTAN EN LA LISTA
    suma = 0
    repetidos = 0
    logger.debug('Inicio Subrutina APPEND')
    n=len(lista1)-1
    while n >= 0:
        if len(lista)==0:
            lista=lista1[:]

        esta = False
        Loop_fin = False
        for m in xrange(len(lista)-1,-1,-1):
            if lista1[n][2] == lista[m][2] and lista1[n][6] == lista[m][6] and lista1[n][11] == lista[m][11]:
               esta = True
               break;
        if esta == False:
                print "Anadiendo ...",lista1[n]
                logger.debug('Anadiendo '+ str(lista1[n]))
                lista.append(lista1[n])
                suma = suma +1
                while lista1[n][2] == lista1[n-1][2] and lista1[n][6] == lista1[n-1][6] and lista1[n][11] == lista1[n-1][11] and n >= 1:
                    n = n - 1
                    lista.append(lista1[n])
                    print "Repetido: ",lista1[n]
                    logger.debug('Repetido '+ str(lista1[n]))
                    repetidos = repetidos + 1
                    suma = suma + 1
                    if n == 0: break
                n = n + 1  #dejar el puntero en posicion correcta
        n = n - 1


    logger.debug('Borrados -- A�adidos - Repetidos....:' + str(delete) + '    ' + str(suma)+ '    '+str(repetidos))



def atack_list():
    global lista1
    global servertime_HTML

#    logger.debug('Identificando')
    unidad=""
    for n in lista1:
        try:
            ox,oy=n[2].split("|")
            ox=int(ox)
            oy=int(oy)
            dx,dy=n[6].split("|")
            dx=int(dx)
            dy=int(dy)
            fields=math.hypot(math.fabs(dx-ox),math.fabs(dy-oy))
            tiempo=int(n[8])
            if (35*60)*fields > tiempo:
                       unidad = "CONDE"
            if (30*60)*fields > tiempo:
                      unidad = "Arietes"
            if (20*60)*fields > tiempo:
                      unidad = "Milicia"
            if (18*60)*fields > tiempo:
                      unidad = "Bersekers"
            if (11*60)*fields > tiempo:
                      unidad = "Negros"
            if (10*60)*fields > tiempo:
                      unidad = "Cruzados"
            if (9*60)*fields > tiempo:
                      unidad = "Espias"
            if (1*1)*fields > tiempo:
                      unidad = "Reconquista"

            if len(n) <= 9:
                      n.append(unidad)
                      n.append(tiempo)
                      n.append(tiempo+int(servertime_HTML))
                      n.append(False)

        except Exception, e:
            print "Error en: ",n, e


def extract_attacks(text):
        """
        Extrae de la variable text, el jugador atacado, ataquente coordenadas y tiempo
        Nos devuelve una lista de listas con todos los datos de cada ataque
        """

        global servertime_HTML
        global lista1

#        logger.debug('Extrayendo')
        text=text[text.find("serverTime = "):]
        servertime_HTML=int(text[13:text.find(";")])
        Hora_server = time.ctime(servertime_HTML)
        print Hora_server
        logger.debug(Hora_server)

        inicio = time.time()
        text=text[text.find("</h1"):]
        while text.find("time")>1:
            ataque = []
            text=text[text.find("player"):]
	    text=text[text.find(">")+1:]
	    jugador = text[:text.find("</a>")]
	
	    text=text[text.find("village"):]
	    text=text[text.find(">")+1:]
	    destino=text[:text.find("(")]

	    text=text[text.find("</a>")-12:]
	    map_dest=text[text.find("</a>")-12:7]
	
	    text=text[text.find("player"):]
	    text=text[text.find(">")+1:]
	    atacante=text[:text.find("</a>")]
	
	
            if text[:100].find("ally")>=0:
                text=text[text.find("ally"):]
                text=text[text.find(">")+1:]
	        ally=text[:text.find("</a>")]
            else:
                ally="Sin alianza"

	    text=text[text.find("village"):]
	    text=text[text.find(">")+1:]
	    origen=text[:text.find("(")]
	
	    text=text[text.find("</a>")-12:]
	    map_ori=text[text.find("</a>")-12:7]
	
	    text=text[text.find("<td class"):]
	    text=text[text.find(">")+1:]
	    hora=text[:text.find("</td>")]

	    text=text[text.find("time"):]
	    text=text[text.find(chr(34))+1:]
	    timer=text[:text.find(chr(34))]
	    final=time.time() - inicio	
	
	
            if jugador !="":
        	ataque.append(jugador)
	        ataque.append(destino)
	        ataque.append(map_dest)
	        ataque.append(atacante)
   	        ataque.append(ally)
	        ataque.append(origen)
	        ataque.append(map_ori)
  	        ataque.append(hora)
         	ataque.append(timer)
         	lista1.append(ataque)

def extract_attacks_HTML(text):
        """
        Extrae de la variable text, el jugador atacado, ataquente coordenadas y tiempo
        Nos devuelve una lista de listas con todos los datos de cada ataque
        """

        a=time.time()
        global regexObj
        global servertime_HTML
        global lista1

        ataques_HTML = None
        re_player= ".*?info\_player.*?>(.*?(?=</a>)).*?</a>"
        re_village=".*?info\_village.*?>(.*?)\(.*?(\d\d\d\|\d\d\d)"
        re_ally=   ".*?info\_ally.*?>(.*?(?=</a>)).*?</a>"
        re_arrive_time=".*?\">(.*?)</td>"
        re_time=   ".*?time.*?([0-9]+)\".*?"
        #regexp = "<a.*?>(.*?)</a>.*?<a.*?>(.*?)</a>.*?(\d\d\d\|\d\d\d).*?<a.*?>(.*?)</a>.*?<a.*?>(.*?)</a>.*?<a.*?>(.*?)</a>.*?(\d\d\d\|\d\d\d).*?<td.*?>(.*?)</td>.*?time=.*?([0-9]+)"

        ataques_HTML = re.findall(".*?list.*?command/attack\.png" + re_player +  re_village + re_player + re_ally + re_village + re_arrive_time +re_time , text, re.DOTALL | re.MULTILINE)
        #ataques_HTML = re.findall(regexp , text, re.DOTALL | re.MULTILINE)


        re_servertime=".*?servertime.*?time.*?([0-9]+).*?"
        servertime_HTML = re.findall(re_servertime,text)

        #Revisa que los antiguos ataques no esten, solo apareceran los vigentes en la bandera
        #new_attacks=[]
        #for n in ataques_HTML:
        #    new_attacks.append(list(n))
        #remove_not_exists(list(new_attacks))

        #Inserta los nuevos ataques que no existen en la lista y que han aparecido en la bandera

        for n in ataques_HTML:
              #if exists_attack(list(n))==False:
              # print "Se añae .....", list(n)
              lista1.append(list(n))
        print time.time()-a


def write_HTML():

    global servertime_HTML
    global lista
    icon = {"Espias":"unit_spy.png",
    "Milicia":"unit_farmer.png",
    "Arietes":"unit_ram.png",
    "Cruzados":"unit_light.png",
    "Negros":"unit_heavy.png",
    "CONDE":"unit_snob.png",
    "Bersekers":"unit_axe.png",
    "Reconquista":"reconquista.jpg"}
    f=open( directorio + "/index.html","w")
    f.write("""<?xml version="1.0" encoding="UTF-8"?>""")
    f.write("""<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">""")
    f.write("<html>")
    f.write(HEAD_HTML)  # empieza y termina HEAD
    f.write("""<body id="ally_attacks"><a><br>""" )  # empieza el BODY

    f.write("Ultima actualizacion del servidor: " + time.asctime(time.localtime(int(servertime_HTML))))
    f.write("""<BR><BR><BR><FONT COLOR="red" size="10"><P ALIGN=center>Total de de ataques: """ + str(len(lista)) +"</FONT><BR><BR><BR><BR><BR>")
    f.write("""<a href="index1.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    f.write("""<button> Ataques ordenador por colonia ATACANTE</button></a><BR><BR>""")
    f.write("""<a href="index2.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    f.write("""<button> Ataques ordenados por colonia Destino</button></a><BR><BR>""")
    f.write("""<FONT COLOR="black">Corregido hora de llegada ataque ORDENADOS</FONT><BR>""")
    f.write("""<FONT COLOR="black">A&ntilde;adido Hora Exacta a todas las tablas</FONT><BR>""")
    f.write("""<FONT COLOR="black"></FONT></a><BR><BR>""")
    f.write("""<div class="contentpane">""")
    f.write("""<table align="center" style="width:820px"><tr><td>""")

    #####################
    # Diccionario jugador
    #####################
    x = {}
    g = []
    for n in lista:
        if n[4] == u"[" + ally + "]": continue
        try:
           x[n[0]] = x[n[0]] + 1
        except:
           x[n[0]] = 1
    for b in x.iteritems():
        g.append(list(b))
    g = sorted(g, key=operator.itemgetter(1))

    # crea lso botones en la parte de la página de los jugadores atacados
    for jugador in g:
        player = jugador[0].encode("utf-8")
        f.write("<a href='#" + player + "'><button>" + player + " (<span style='color: #FF0000'>" + str(jugador[1])  + "</span>)</button></a>")
    n_ataques = 0

    ########## inicio HTML de  absorciones
    f.write("""
</td></tr></table>
<h1><p align=center>Absorciones</p></h1>
<table align="center">
   <tr><td>
<table class="borderlist"  style="width:820px">
   <tbody>
   <tr>
   <th align="center">Ordenar</th>
   <th align="center">Destino</th>

   <th align="center">Origen</th>
   <th align="center">Llegada</th>
   <th align="center">Hora exacta</th>
   </tr>

   <tr>
""")

    n_ataques = 0
    for n in lista:
       if n[4] != u"[" + ally + "]": continue
       n_ataques = n_ataques + 1
       f.write("""<td class="list1" align="center"><img src='""" + icon[n[9]] + "'></td>")
       f.write("""<td class="list1">""")
       f.write("<a>" + n[0].encode("utf-8") +"</a><br /><a>" + n[1].encode("utf-8") + "  " + n[2].encode("utf-8") + "</a></td>" )
       f.write("""<td class="list1">""")
       f.write("<a>" + n[3].encode("utf-8") + "  " + n[4].encode("utf-8") +"</a><br /><a>" + n[5].encode("utf-8") + "</a></td>" )
       f.write("""<td class="list1" align="center">""" + n[7] + "</td>")
       tiempo = n[11]-3600*7
       f.write("""<td class="list1" align="center">""" + time.strftime("%H:%M:%S",time.localtime(tiempo)) + "</td></tr>")
    f.write("""</tbody></td></tr></table></table>""")
    f.write("""<p align=center><FONT COLOR="red">Numero de ataques: """ + str(n_ataques) +"</FONT></p><BR><BR><BR>")
    ######### fin HTML absorciones


    #############################################
    ########### POR JUGADOR #####################
    #############################################
    for jugador in g:
        f.write(chr(13))
        f.write("<a id='"  + jugador[0].encode("utf-8") + "' name='"  + jugador[0].encode("utf-8") +"'>")
        f.write("<h2><P ALIGN=center>Ataques a " + jugador[0].encode("utf-8") + "</h2>")
        f.write("""
        <table align="center">
            <tr><td>
            <table class="borderlist"  style="width:820px">
            <tbody>
             <tr>
              <th align="center">Unidad</th>
              <th align="center">Destino</th>
              <th align="center">Origen</th>
              <th align="center">Llegada</th>
              <th align="center">Hora exacta</th>
            </tr>
            <tr>""")

        n_ataques=0
        for n in lista:
            if n[0].encode("utf-8") <> jugador[0].encode("utf-8") or n[4] == u"[" + ally + "]": continue
            n_ataques=n_ataques+1
            f.write("""<td class="list1" align="center"><img src='""" + icon[n[9]] + "'></td>")  #Unidad
            f.write("""<td class="list1">""")
            f.write("<a>" + n[0].encode("utf-8") +"</a><br /><a>" + n[1].encode("utf-8") + "  " + n[2].encode("utf-8") + "</a></td>" ) #Destino
            f.write("""<td class="list1">""")
            f.write("<a>" + n[3].encode("utf-8") + "  " + n[4].encode("utf-8") +"</a><br /><a>" + n[5].encode("utf-8")  + "</a></td>" ) #Origen
            f.write("""<td class="list1" align="center">""" +  n[7]  + "</td>")  #Llegada
            #d,h,m,s = convert_time(n[8])
            #f.write("""<td class="list1" align="center">""" + "%d dia   %02d:%02d:%02d" %(d,h,m,s) + "</td></tr>")
            tiempo = n[11]-3600*7
            f.write("""<td class="list1" align="center">""" + time.strftime("%H:%M:%S",time.localtime(tiempo)) + "</td></tr>") #Hora Exacta
        f.write("""</tbody></td></tr></table></table><P ALIGN=center><a href="BB-Codes_""" + jugador[0].encode("utf-8") + """.html" stye="color: #0000FF"   target="_blank""><button>BB-Codes</button></a>""")
        f.write("""<FONT COLOR="red">Numero de ataques: """ + str(n_ataques) +"</FONT><BR><BR><BR>")
        f.write("<BR><BR><BR></a>")
        crea_BBCodes(jugador[0].encode("utf-8"))

    f.write("</table>")
    f.write("</div></BODY>")
    f.close()
    crea_HTMLOrigen()
    crea_HTMLDestino()


def crea_HTMLOrigen():
    global servertime_HTML
    global lista
    icon = {"Espias":"unit_spy.png",
    "Milicia":"unit_farmer.png",
    "Arietes":"unit_ram.png",
    "Cruzados":"unit_light.png",
    "Negros":"unit_heavy.png",
    "CONDE":"unit_snob.png",
    "Bersekers":"unit_axe.png",
    "Reconquista":"reconquista.jpg"}
    index1=open( directorio + "/index1.html","w")
    index1.write("""<?xml version="1.0" encoding="UTF-8"?>""")
    index1.write("""<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">""")
    index1.write("<html>")
    index1.write(HEAD_HTML)  # empieza y termina HEAD
    index1.write("""<body id="ally_attacks"><a><br>""" )  # empieza el BODY

    index1.write("Ultima actualizacion del servidor: " + time.asctime(time.localtime(int(servertime_HTML))))
    index1.write("""<BR><BR><BR><FONT COLOR="red" size="10"><P ALIGN=center>Total de de ataques: """ + str(len(lista)) +"</FONT><BR><BR><BR><BR><BR>")
    index1.write("""<a href="index.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    index1.write("""<button> Ataques ordenados por jugador</button></a><BR><BR>""")
    index1.write("""<a href="index2.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    index1.write("""<button> Ataques ordenados por colonia Destino</button></a><BR><BR>""")

    index1.write("""<FONT COLOR="black">Corregido hora de llegada ataque ORDENADOS</FONT><BR>""")
    index1.write("""<FONT COLOR="black">A&ntilde;adido Hora Exacta a todas las tablas</FONT><BR>""")
    index1.write("""<FONT COLOR="black"></FONT></a><BR><BR>""")
    index1.write("""<div class="contentpane">""")
    index1.write("""<table align="center" style="width:820px"><tr><td>""")

    #####################
    # Diccionario jugador
    #####################
    x = {}
    h = []
    for n in lista:
        if n[4] == u"[" + ally + "]": continue
        try:
           x[n[5]] = x[n[5]] + 1
        except:
           x[n[5]] = 1

    for b in x.iteritems():
        h.append(list(b))
    h = sorted(h, key=operator.itemgetter(1))

    #############################################
    ########### POR ORIGEN  #####################
    #############################################
    for jugador in h:
        index1.write(chr(13))
        index1.write("<a id='"  + jugador[0].encode("utf-8") + "' name='"  + jugador[0].encode("utf-8") +"'>")
        index1.write("<h2><P ALIGN=center>Ataques Desde ----> " + jugador[0].encode("utf-8") + "</h2>")
        index1.write("""
        <table align="center">
            <tr><td>
            <table class="borderlist"  style="width:820px">
            <tbody>
             <tr>
              <th align="center">Unidad</th>
              <th align="center">Destino</th>
              <th align="center">Origen</th>
              <th align="center">Llegada</th>
              <th align="center">Hora exacta</th>
            </tr>
            <tr>""")

        n_ataques=0
        for n in lista:
            if n[5].encode("utf-8") <> jugador[0].encode("utf-8") or n[4] == u"[" + ally + "]": continue
            n_ataques=n_ataques+1
            index1.write("""<td class="list1" align="center"><img src='""" + icon[n[9]] + "'></td>")  #Unidad
            index1.write("""<td class="list1">""")
            index1.write("<a>" + n[0].encode("utf-8") +"</a><br /><a>" + n[1].encode("utf-8") + "  " + n[2].encode("utf-8") + "</a></td>" ) #Destino
            index1.write("""<td class="list1">""")
            index1.write("<a>" + n[3].encode("utf-8") + "  " + n[4].encode("utf-8") +"</a><br /><a>" + n[5].encode("utf-8")  + "</a></td>" ) #Origen
            index1.write("""<td class="list1" align="center">""" +  n[7]  + "</td>")  #Llegada
            tiempo = n[11]-3600*7
            index1.write("""<td class="list1" align="center">""" + time.strftime("%H:%M:%S",time.localtime(tiempo)) + "</td></tr>") #Hora Exacta
        index1.write("""</tbody></td></tr></table></table><P ALIGN=center><a href="BB-Codes_""" + jugador[0].encode("utf-8") + """.html" stye="color: #0000FF"   target="_blank""><button>BB-Codes</button></a>""")
        index1.write("""<FONT COLOR="red">Numero de ataques: """ + str(n_ataques) +"</FONT><BR><BR><BR>")
        index1.write("<BR><BR><BR></a>")

    index1.write("</table>")
    index1.write("</div></BODY>")
    index1.close()



def crea_HTMLDestino():
    global servertime_HTML
    global lista
    icon = {"Espias":"unit_spy.png",
    "Milicia":"unit_farmer.png",
    "Arietes":"unit_ram.png",
    "Cruzados":"unit_light.png",
    "Negros":"unit_heavy.png",
    "CONDE":"unit_snob.png",
    "Bersekers":"unit_axe.png",
    "Reconquista":"reconquista.jpg"}
    index1=open( directorio + "/index2.html","w")
    index1.write("""<?xml version="1.0" encoding="UTF-8"?>""")
    index1.write("""<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">""")
    index1.write("<html>")
    index1.write(HEAD_HTML)  # empieza y termina HEAD
    index1.write("""<body id="ally_attacks"><a><br>""" )  # empieza el BODY

    index1.write("Ultima actualizacion del servidor: " + time.asctime(time.localtime(int(servertime_HTML))))
    index1.write("""<BR><BR><BR><FONT COLOR="red" size="10"><P ALIGN=center>Total de de ataques: """ + str(len(lista)) +"</FONT><BR><BR><BR><BR><BR>")
    index1.write("""<a href="index.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    index1.write("""<button> Ataques ordenados por jugador</button></a><BR><BR>""")
    index1.write("""<a href="index1.html" target="_self" stye="color: #0000FF" align=center  target="_blank"">""")
    index1.write("""<button> Ataques ordenados colonia Origen</button></a><BR><BR>""")
    index1.write("""<FONT COLOR="black">Corregido hora de llegada ataque ORDENADOS</FONT><BR>""")
    index1.write("""<FONT COLOR="black">A&ntilde;adido Hora Exacta a todas las tablas</FONT><BR>""")
    index1.write("""<FONT COLOR="black"></FONT></a><BR><BR>""")
    index1.write("""<div class="contentpane">""")
    index1.write("""<table align="center" style="width:820px"><tr><td>""")

    #####################
    # Diccionario jugador
    #####################
    x = {}
    h = []
    for n in lista:
        if n[4] == u"[" + ally + "]": continue
        try:
           x[n[1]] = x[n[1]] + 1
        except:
           x[n[1]] = 1

    for b in x.iteritems():
        h.append(list(b))
    h = sorted(h, key=operator.itemgetter(1))

    #############################################
    ########### POR ORIGEN  #####################
    #############################################
    for jugador in h:
        index1.write(chr(13))
        index1.write("<a id='"  + jugador[0].encode("utf-8") + "' name='"  + jugador[0].encode("utf-8") +"'>")
        index1.write("<h2><P ALIGN=center>Ataques que llegan a ----> " + jugador[0].encode("utf-8") + "</h2>")
        index1.write("""
        <table align="center">
            <tr><td>
            <table class="borderlist"  style="width:820px">
            <tbody>
             <tr>
              <th align="center">Unidad</th>
              <th align="center">Destino</th>
              <th align="center">Origen</th>
              <th align="center">Llegada</th>
              <th align="center">Hora exacta</th>
            </tr>
            <tr>""")

        n_ataques=0
        for n in lista:
            if n[1].encode("utf-8") <> jugador[0].encode("utf-8") or n[4] == u"[" + ally + "]": continue
            n_ataques=n_ataques+1
            index1.write("""<td class="list1" align="center"><img src='""" + icon[n[9]] + "'></td>")  #Unidad
            index1.write("""<td class="list1">""")
            index1.write("<a>" + n[0].encode("utf-8") +"</a><br /><a>" + n[1].encode("utf-8") + "  " + n[2].encode("utf-8") + "</a></td>" ) #Destino
            index1.write("""<td class="list1">""")
            index1.write("<a>" + n[3].encode("utf-8") + "  " + n[4].encode("utf-8") +"</a><br /><a>" + n[5].encode("utf-8")  + "</a></td>" ) #Origen
            index1.write("""<td class="list1" align="center">""" +  n[7]  + "</td>")  #Llegada
            tiempo = n[11]-3600*7
            index1.write("""<td class="list1" align="center">""" + time.strftime("%H:%M:%S",time.localtime(tiempo)) + "</td></tr>") #Hora Exacta
        index1.write("""</tbody></td></tr></table></table><P ALIGN=center><a href="BB-Codes_""" + jugador[0].encode("utf-8") + """.html" stye="color: #0000FF"   target="_blank""><button>BB-Codes</button></a>""")
        index1.write("""<FONT COLOR="red">Numero de ataques: """ + str(n_ataques) +"</FONT><BR><BR><BR>")
        index1.write("<BR><BR><BR></a>")

    index1.write("</table>")
    index1.write("</div></BODY>")
    index1.close()


################################################################
# Crea la pagina HTML para copiar los BBCodes en el portapapeles
################################################################
def crea_BBCodes(jugador):
        icon_foro = {"Espias":"[img_spy]",
        "Milicia":"[img_farmer]",
        "Arietes":"[img_ram]",
        "Cruzados":"[img_light]",
        "Negros":"[img_heavy]",
        "CONDE":"[img_snob]",
        "Bersekers":"[img_axe]",
        "Reconquista":"[img_spy][img_spy]"}
        archivo= directorio + "/BB-Codes_" + jugador + ".html"
        j=open(archivo,"w")
        j.write("""
<head>
<title>KingsAge - BB-Codes</title>
<meta http-equiv="Content-Language" content="de" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>""")
        j.write("<P>")
        for m in lista:
            if m[0].encode("utf-8") <> jugador: continue
            j.write(icon_foro[m[9]] +  " " +  time.strftime("%H:%M:%S",time.localtime(m[11])) + " " + jugador + "  [village]" + m[2].encode("utf-8") + "[/village]  " + "  [village]" + m[6].encode("utf-8") + "[/village]  " + "  [player]" + m[3].encode("utf-8") + "[/player]  " +"<BR>")
        j.write("""</P><FONT COLOR="red">Una vez copiado en el portapapeles puedes cerrar esta ventana</FONT>""")
        j.close()





####################################################################3
####################################################################3
################ P R I N C I P A L #################################3
####################################################################3
####################################################################3

servertime_HTML = ""
lista=[]
lista1=[]

HEAD_HTML="""
<head>
    <title>KingsAge - Ataques ally</title>
    <meta http-equiv="Content-Language" content="de" />
    <meta http-equiv="Refresh" "content="60" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="Vers0.css" />

    <style style="text/css">
     body {
     background-image: url("img/layout/lay_content.jpg");
     background-size: cover;
     background-repeat: repeat;
     }
     </style>
</head>
"""




cabecera = {'User-Agent':' Mozilla/5.0 (X11; U; Linux i686; es-ES; rv:1.9.0.14) Gecko/2009091010 Firefox/3.0.10',
'Accept': 'text/css,*/*;q=0.1',
'Accept-Language': 'es-es,es;q=0.8,en-us;q=0.5,en;q=0.3',
'Accept-Encoding': 'gzip,deflate',
'Accept-Charset': 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
'Keep-Alive': '300',
'Connection': 'keep-alive',
'Referer': server +'/game.php',
'Cookie': "KingsAge-Game=" + KingsAge_Game + "; SD_FRAMEWORK_SESSION=" + SD_FRAMEWORK_SESSION + "; game_hash=" + game_hash +"; game_user=" + usuario + "; game_pass=" + password
 }


logger.debug('Arrancando')

page_fake_ini = server + "/game.php?village=" + pueblo_fake


page_fake=[page_fake_ini + "&s=build_main",
page_fake_ini + "&s=build_barracks",
page_fake_ini + "&s=map",
page_fake_ini + "&s=info_village&id=1",
page_fake_ini + "&s=info_village&id=109",
page_fake_ini + "&s=build_market",
page_fake_ini + "&s=overview",
page_fake_ini + "s=ally&m=attacks"]



try:
    lista = pickle.load(open(path + "respaldo_ataques_11"))
except:
    print "No se pudo abrir archivo de respaldo"

b=0

while (1):
     inicio = time.time()
     t_azar = random.randrange(30,60) # tiempo entre accesos
     request = True
     done = False

     while (request):
        try:
          time.sleep(15)
	  dif_time = time.time() - inicio
	  index_page = random.randrange(0,7)
	  if dif_time > t_azar and done == False:
		 response,content =  h.request(page_fake[index_page], 'GET', headers=cabecera)
		 done = True
		 print page_fake[index_page]

          if dif_time >= 30:
                 inicio = time.time()
	         lista1 = []
	         response,html =  h.request(page_fake_ini + "&s=ally&m=attacks", 'GET', headers=cabecera)
		 html=html.decode("utf-8",'ignore')
		 if html.find("incorrec")>0:
		  print "incorrecta"
		  break
		 if html.find("ha caducado")>0:
		  print "caducada"
		  break
		 print response
		 html_temp=html
		 extract_attacks(html)
		 atack_list()
                 logger.debug('Leyendo inicio bandera')
		 page = 50
		 pages = re.findall("m=attacks\&start\=(\d+)\"\>", html_temp, re.DOTALL | re.MULTILINE)
                 logger.debug('Leyendo PAGINAS =' + str(pages))
                 print page,pages
		 while (str(page) in pages):
		                  response,html =  h.request(page_fake_ini + "&s=ally&m=attacks&start=" + str(page) , 'GET', headers=cabecera)
  		                  html=html.decode("utf-8",'ignore')
		                  html_temp=html
		                  extract_attacks(html)
		                  atack_list()
		                  logger.debug('Leyendo bandera pagina =' + str(page))
   		                  page = page + 50
		                  pages = re.findall("m=attacks\&start\=(\d+)\"\>", html_temp, re.DOTALL | re.MULTILINE)
		                  #print page,pages

                 if servertime_HTML.__class__ == int and lista1!=[]:
                     print servertime_HTML.__class__
                     remove_not_exist()
		     lista = sorted(lista, key=operator.itemgetter(11,7))
		     print "Escribiendo HTML Mundo 16 MEXICO"
                     write_HTML()
                     r=open(path + "respaldo_ataques_22","w")
		     pickle.dump(lista,r)
		     r.close()
		     logger.debug('Grabado Fichero\n\n')
                 else:
                     f=open( directorio + "/index.html","w")
                     f.write(
                     """
                     <?xml version="1.0" encoding="UTF-8"?>
                     <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
                     <html>
                     <head>
                     <title>KingsAge - Ataques ally</title>
                     <meta http-equiv="Content-Language" content="de" />
                     <meta http-equiv="Refresh" "content="60" />
                     <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                     <link rel="stylesheet" type="text/css" href="Vers0.css" />

                     <style style="text/css">
                     body {
                     background-image: url("img/layout/lay_content.jpg");
                     background-size: cover;
                     background-repeat: repeat;
                     }
                     </style>
                     </head>

                     <body id="ally_attacks">
                     <a><br>""")
                     f.write("Ultima actualizacion del servidor: " + time.asctime(time.localtime(int(servertime_HTML))))
                     f.write("""<BR><BR><BR><FONT COLOR="red" size="10"><P ALIGN=center>Total de de ataques: 0</FONT><BR><BR><BR>""")
                     f.write("""<BR><BR><FONT COLOR="black">Corregido Bandera sin ataques</FONT><BR></body></html>""" )
                     f.close()

        except (socket.timeout,httplib2.HttpLib2Error,httplib.BadStatusLine):
                 logger.debug('ERROR SOCKET TIMEOUT')
                 print "Timeout socket"
                 pass
        except IOError:
               print "No se pudo guardar el archivo de respaldo_11"
               logger.debug('No se pudo guardar el archivo de respaldo')

        except:
                print "Unexpected error:", sys.exc_info()[0]
                raise
                logger.debug('ERROR GENERAL')
                #pass
