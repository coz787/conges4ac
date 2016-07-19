#!/usr/bin/env python 
# -*- coding: utf-8 -*-  
'''cg_minst.py: conges multi-installer 
outil d'installation multiple adapté au portage des applications 
conges d'un env vers un autre 
'''

import getpass, sys, os, subprocess, pprint, string, re, time
from traceback import print_exc
from cg_lib import exec_sql_file
import MySQLdb

lcmde = ['dryrun', 'realrun'] 
scmd = ""   
version = "1_0"

def usage(cmd):
    fmt = ''' Usage: %s \
[--help] [--debug] --conf=<filename> --cmd=commande  
conf file should define 
{ 
'rootdb': {'id':'root', 'pw':'' },
# pw saisie en interactif 
'cdbprefixe': 'r1_', 
'cdbcharset': 'latin1',
# les base de donnees seront nomme RRRRR_sg 
'crefpath' : '/tmp/work/v2ns',
# endroit ou est installer logiciel de reference
'cdbconnectpath' : 'dbconnect.php',
# path relatif au fichier dbconnect.php
'csqlinstallpath' : 'install/sql/php_conges_v1.4.2ac2_01.sql',
# path relatif au fichier sql de creation initiale de la base de donnees
'cdestpath': '/tmp/work/srv/www/conges', 
# endroit ou seront installes les instances logicielles 
'clinstance': [ ['sg','c_sg_dba','******'] , 
                ['dsacn','c_dsacn_dba','******'] , 
]
# liste des instances : nom, usergestionnaire, passwd 
# si passwd est "", il sera demandé en interactif 
}

cmd among [ %s ]  
'''
    print fmt   %  (cmd, string.join(lcmde," ")) 

def neatconflines(ofile): 
    lrealine = [] 
    while 1 :
        sali = ofile.readline()
        if sali == "" : break
        elif sali[0] == "#" : pass # discard comment 
        else:
            lrealine.append(sali[:-1])
    return string.join(lrealine)

def mygetopt(cmd,largs):
    ''' process argument and if success, return	
command name, debug mode , odbifile= database access, la commande , 
un dictionnaire avec les options non traitees '''
    lpathitem = string.split(sys.argv[0],'/')
    sacmd = lpathitem[-1]
    scom = "dryrun" # default 
    debug = 0
    odbifile =  None
    dallopt, dropt = {}, {}  
    idx = 0 
    soptegex1 = re.compile('^--([^\=]+)\=(.*)')
    soptegex2 = re.compile('^--(.*)')
    while idx < len(largs) :
        s1m = soptegex1.match(largs[idx])
        if s1m : 
            dallopt[s1m.group(1)] = s1m.group(2)
        else :
            s2m = soptegex2.match(largs[idx])
            if s2m : 
                dallopt[s2m.group(1)] = 1
            else :
                print "arg %s is not well formatted " % largs[idx]
                usage(sacmd)
                sys.exit(1)
        idx += 1 
    for (skey,svalue) in dallopt.items() :
        if skey == "help" :
            usage(sacmd)
            sys.exit(1)
        elif skey == "debug":
            debug = 1
        elif skey == "conf" :
            try:
                odbifile = open(svalue)
            except:
                print "conf file %s cannot be opened" % svalue
                usage(sacmd)
                sys.exit(1)
        elif skey  ==  "cmd":
            scom = svalue
            if scom not in lcmde :
                print " a commande among %s should be given" % string.join(lcmde," ")
                usage(sacmd)
                sys.exit(1)
        else:
            dropt[skey] = svalue
    if odbifile == None :
        print "--dbid option is mandatory"
        usage(sacmd)
        sys.exit(1)
    if debug: 
        print "dallopt is ", dallopt
        print "dropt is ", dropt 
    return sacmd, debug, odbifile, scom, dropt

def subcmd(lcommand,santemsg,saftermsg):
    sys.stdout.write(santemsg)
    sys.stdout.flush()
    op = subprocess.Popen(lcommand,stdout=subprocess.PIPE,stderr=None)
    output, err = op.communicate()
    sys.stdout.write(' '+saftermsg+'\n')
    sys.stdout.flush()
    # op = subprocess.Popen(lcommand, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE) 
    # stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    # output, err = op.communicate()  # capture stdout n stderr but do not display 
    # status =
    # subprocess.check_call(lcommand, shell=True)
    #if status :
    #     sys.stdout.write("status: %s\n" % status)
    # print santemsg + " ", 
    # status = subprocess.call(lcommand, shell=True) 
    # print saftermsg

def patch_dbconnect(dirpath, filename,dpara): 
    ''' patch dbconnect.php to substitute operational variable 
$mysql_user="__CONGEDBA__" ;
$mysql_pass="__CONGEPW__";
$mysql_database= "__DBNAME__" ; ''' 
    lpattern = [["__CONGEDBA__" , dpara['dbauser']],
                ["__CONGEPW__"  , dpara['dbapw']] , 
                ["__DBNAME__"   , dpara['dbname']],
                ["__CHARSET__"   , dpara['charset']],
                ]  
    sfnname = "%s/%s" % (dirpath,filename)
    ssavname = sfnname + '.sav' 
    try:
        os.rename(sfnname,ssavname)
    except:
        print_exc()
        print "%s file name cannot be renamed" % sfnname
        sys.exit(1)
    infile = open(ssavname, 'r')
    outfile = open(sfnname, 'w')
    # methode assez lourde mais bon ... 
    while True:
        aline = infile.readline()
        if aline == "" :
            break 
        newline = aline 
        for apat in lpattern :
            newline = string.replace(newline,apat[0],apat[1])
        outfile.write(newline)
    print "%s was patched" % sfnname
    infile.close()
    outfile.close()

if __name__ == '__main__':
    #0 get parameter/arg 
    scmd, ndebug, odbif, scommand, dopt = mygetopt(sys.argv[0],sys.argv[1:])
    #1.1 read conf file and check it brieffly  
    try:
        dcgid = eval(neatconflines(odbif))
    except:
        print_exc()
        print "database id file cannot be evaluated"
        sys.exit(1)
    if ndebug: print pprint.pformat(dcgid)
    #1.2 ask root access and check access to mysql/mariadb 
    smysqlrpw = getpass.getpass("mysql root pw:")
    try: 
        dcgid['rootdb']['pw'] = smysqlrpw
    except:
        print "conf file does not define ['rootdb']['pw'] key "
        sys.exit(1)
    odbconn_std, odbcursor_std = None, None 
    try:
        odbconn_std = MySQLdb.connect( 
            "localhost",dcgid['rootdb']['id'],dcgid['rootdb']['pw'],
            charset=dcgid['cdbcharset'], use_unicode=1)
        odbcursor_std = odbconn_std.cursor() 
    except:
        # print_exc()
        print "root acces to mysql refused" 
        sys.exit(1)
    if ndebug: print "root acces to mysql ok."
    #1.3 check validity of crefpath 
    screfpath = None
    try: 
        screfpath = dcgid['crefpath']
    except:
        print "ref path is not defined in conf file"
        sys.exit(1)
    # make sure screfpath does not end with / 
    nj = len(screfpath) 
    while nj > 0 :
        if screfpath[nj - 1] == '/' :
            nj -= 1 
        else :
            break 
    screfpath = screfpath[0:nj] 
        
    if not os.access(screfpath, os.F_OK & os.R_OK) :
        print "read acces to %s conges ref path refused" % dcgid['crefpath']
        sys.exit(1)

    screfpathdbconnect = None
    try: 
        screfpathdbconnect = "%s/%s" % (dcgid['crefpath'],dcgid['cdbconnectpath'])
    except:
        print "ref path is not defined in conf file"
        sys.exit(1)
    if not os.access(screfpathdbconnect, os.F_OK & os.R_OK) :
        print "read acces to %s file refused" % screfpathdbconnect
        sys.exit(1)

    scsqlinstallpath = None
    try: 
        scsqlinstallpath = "%s/%s" % (dcgid['crefpath'],dcgid['csqlinstallpath'])
    except:
        print "csqlinstallpath is not defined in conf file"
        sys.exit(1)
    if not os.access(scsqlinstallpath, os.F_OK & os.R_OK) :
        print "read acces to %s file refused" % scsqlinstallpath
        sys.exit(1)

    sdestpath = None 
    try: 
        sdestpath = dcgid['cdestpath']         
    except:
        print "dest path is not defined in conf file"
        sys.exit(1)
    if not os.access(sdestpath, os.W_OK) :
        print "write acces to %s conges dest path refused" % dcgid['cdestpath']
        sys.exit(1)

    #1.4 check and complete linstance definition 
    #    (passwd captured interactively if required)
    try:
        leninstance = len(dcgid['clinstance'])
    except:
        print "'clinstance' is not defined in conf file"
        sys.exit(1)
    if leninstance == 0 :
        print "'clinstance' shoud defined one target app"
        sys.exit(1)
    linstnew = [] 
    for sappname,sdba,sdbapass in dcgid['clinstance'] :  # iterating over clinstance : 
        while sdbapass == "" :
            sdbapass = getpass.getpass("mysql pw for %s:" % sdba)
        linstnew.append([sappname,sdba,sdbapass]) 
    dcgid['clinstance'] = linstnew 
    #print "prematured end." 
    #sys.exit(0)

    lsqlinst = [
"DROP DATABASE IF EXISTS %(dbname)s ;",
"CREATE DATABASE `%(dbname)s` DEFAULT CHARACTER SET %(charset)s DEFAULT COLLATE %(charset)s_general_ci; ",
"GRANT ALL PRIVILEGES ON `%(dbname)s`.* TO '%(dbauser)s'@'localhost' ;",
]
    
    # 2 checking basically clinstance : 
    for sappname,sdba,sdbapass in dcgid['clinstance'] :  # iterating over clinstance : 
        #2.1  issuing sql order 
        try: 
            dpara = {'dbname': dcgid['cdbprefixe']+sappname ,
                     'dbauser': sdba, 'dbapw':sdbapass,
                     'charset': dcgid['cdbcharset'] } 
        except:
            print_exc()
            print "conf file should defined cdbprefixe creader pw cdba pw"
            sys.exit(1)
        for asql in lsqlinst :
            sqlorder = asql % dpara 
            if scommand == "realrun" :
                try:
                    odbcursor_std.execute(sqlorder)
                except:
                    print "mysql error on %s" % sqlorder
                    print_exc()
                    sys.exit(1)
                print "%s ok." % sqlorder
            else :
                print "would do %s" % sqlorder 

        #2.2 duplicating appfile rsync -av v2ref/ v2new
        # alternative a rsync : cp 
        # mkdir /tmp/work/srv/www/conges/sg && (tar -C /tmp/work/v2ns -cvf - . | tar -C /tmp/work/srv/www/conges/sg/ -xf - ) 
        sdirpath = "%s/%s" % (dcgid['cdestpath'], sappname)        
        # ldupcmd = ['ls', '%s/' %  dcgid['crefpath'], 
        # CARE : the terminating / is essential to not create a level of hierarchy 
        ldupcmd = ["rsync", "-av", "%s/" %  dcgid['crefpath'],sdirpath ] 
        saction = string.join(ldupcmd,' ')
        if scommand == "realrun" :
            try:
                subcmd(ldupcmd,saction,"done.")
            except:
                print "sys error on %s" % saction 
                print_exc()
                sys.exit(1)
        else:
            print "would do %s" % saction

        #2.3 patching v2ref/dbconnect.php with the right stuff 
        sfn = "dbconnect.php" 
        if scommand == "realrun" :
            try:
                patch_dbconnect(sdirpath,sfn,dpara)
            except:
                print "error on patching %s/%s" % (sdirpath,sfn)
                print_exc()
                sys.exit(1)
        else:
            print "would patch %s/%s" % (sdirpath,sfn)
        #2.4 executing sqlinstallation for the instance 
        odbcgconn_std, odbcgcursor_std = None, None 
        try:
            odbcgconn_std = MySQLdb.connect( 
                "localhost",dpara['dbauser'],dpara['dbapw'],
                dpara['dbname'],
                charset=dcgid['cdbcharset'], use_unicode=1)
            odbcgcursor_std = odbcgconn_std.cursor() 
        except:
            # print_exc()
            print "%s acces to mysql (%s) refused" % ( dpara['dbauser'],'******')
            sys.exit(1)
        sqlorder = "use %s ;" % dpara['dbname']
        if scommand == "realrun" :
            try:
                odbcgcursor_std.execute(sqlorder)
            except:
                print "mysql error on %s" % sqlorder
                print_exc()
                sys.exit(1)
            print "%s ok." % sqlorder
        else :
            print "would do %s" % sqlorder 
        # reading and executing sqlfile 
        exec_sql_file(odbcgcursor_std,scsqlinstallpath,scommand)
        # doing a print to separate instance 
        print 

    #end iterating over clinstance :
    print "cdminst ok."
    sys.exit(0)
