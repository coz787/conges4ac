#!/usr/bin/env python 
# -*- coding: utf-8 -*-  
'''cg_etl.py: conges extract transform n load tool
mysql versatile multi-pass transformer tool
script to convert a conges database from one version 
to another in a secured and managed way'''

import sys, os, pprint, string, re, time
from traceback import print_exc
from decimal import Decimal 
from datetime  import date
import MySQLdb
import MySQLdb.cursors

import cg_etl_lib 

scmd = ""   
version = "1_0"
def usage(cmd):
    fmt = ''' Usage: %s \
[--help] [--debug] --scrid=<filename> --cmd=commande [--chelp] [--optioni=sthing]

scrid file should define 'src' :{ 'host':, 'database':, 'charset':,'user':, 'pw': }
                         'dest' :{ 'host':, 'database':, 'charset':,'user':, 'pw': }
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
command name, debug mode , oscrfile= database access, la commande , 
un dictionnaire avec les options non traitees '''
    lpathitem = string.split(sys.argv[0],'/')
    sacmd = lpathitem[-1]
    scom = "" 
    debug = 0
    oscrfile =  None
    sscrfile_out = "" 
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
                print "#error: arg %s is not well formatted " % largs[idx]
                usage(sacmd)
                sys.exit(1)
        idx += 1 
    for (skey,svalue) in dallopt.items() :
        if skey == "help" :
            usage(sacmd)
            sys.exit(1)
        elif skey == "debug":
            debug = 1
        elif skey == "scrid" :
            try:
                oscrfile = open(svalue)
            except:
                print "database id file %s cannot be opened" % svalue
                usage(sacmd)
                sys.exit(1)
            sscrfile_out = svalue + "_out" 
        elif skey  ==  "cmd":
            scom = svalue
            if scom not in lcmde :
                scom = "" 
            if scom == "" :
                print "a command among %s should be given" % string.join(lcmde," ")
                usage(sacmd)
                sys.exit(1)
        else:
            dropt[skey] = svalue
    if oscrfile == None :
        print "--scrid option is mandatory"
        usage(sacmd)
        sys.exit(1)
    if scom == "" :
        print "--cmd is mandatory ; value among (%s) should be given" % string.join(lcmde," ")
        usage(sacmd)
        sys.exit(1)
    if debug: 
        print "dallopt is ", dallopt
        print "dropt is ", dropt 
    return sacmd, debug, oscrfile, sscrfile_out, scom, dropt

oatransfact = None # a subclass of abstransform from cg_etl_lib.py
# lcmde = oatransfact.get_ldometh()
lcmde = ["dryrun", "realrun" ] 
#oadoer = CDoer() 
#lcmde = oadoer.get_ldometh() 

if __name__ == '__main__':
    #0 get parameter/arg 
    scmd, ndebug, oscrif, scrofname, scommand, dopt = mygetopt(sys.argv[0],sys.argv[1:])
    # oadoer.set_debug(ndebug)
    #1.1 get access to database 
    dscrid = {} 
    try:
        dscrid = eval(neatconflines(oscrif))
    except:
        print_exc()
        print "#error: scenario id file cannot be evaluated"
        sys.exit(1)
    if ndebug: print dscrid
    #ddbaccess = {'src': {'cstd':None,'cdict':None},
    #             'dest': {'cstd':None,'cdict':None} }
    #1.2 check data in dscrid 
    try:
        astrat = dscrid['strat']['name']
        ostrat = getattr(cg_etl_lib, astrat)
        #print ostrat
        oatransfact = ostrat() # cree une instance de la classe strategie
        #print oatransfact 
    except:
        print_exc()
        print "#error: scenario file should define a 'strat''name' key"
        print "#error: this scenario name is not a valid AbsTransform class from cg_etl_lib"
        sys.exit(1)
    try:
        dscheme = dscrid['strat']['scheme']
    except:
        print "#error: scenario file should define a scheme as 'strat''scheme'"
        sys.exit(1)
    bvalidclass = False 
    if hasattr(oatransfact, 'tclass'):
        if oatransfact.tclass == "_transform_" :
            bvalidclass = True 
    if not bvalidclass: 
        print "#error: strategy %s is not a valid _transform_ class. " % astrat
        sys.exit(1)
        
    # print pprint.pformat(dscheme)

    ldbtype = ['src','dest'] 
    lrequiredatt = ['host','database','user','pw','charset'] 
    for akdb in ldbtype:
        if not dscrid.has_key(akdb):
            print "#error: databases id file should define %s " % akdb
            sys.exit(1)
        for aatt in lrequiredatt:
            if not dscrid[akdb].has_key(aatt):
                print "#error: databases id file should define attribut %s for %s" % (aatt,akdb)
                sys.exit(1)
    # else everything is provide 
    #1.3 check acess to conges database and build accessor 
    ddbaccess = {} 
    for akdb in ldbtype:
        if dscrid[akdb].has_key('port'):
            nport = dscrid[akdb]['port'] 
        else:
            nport = 3306 # default 
        try:
            o_std = MySQLdb.connect( 
                dscrid[akdb]['host'],dscrid[akdb]['user'],dscrid[akdb]['pw'], 
                dscrid[akdb]['database'], 
                port=nport,
                charset=dscrid[akdb]['charset'], use_unicode=1)
        except:
            print_exc()
            print "#error: acces to %s/%s for user %s refused" % \
                (dscrid[akdb]['host'], dscrid[akdb]['database'], dscrid[akdb]['user'])
            sys.exit(1)
        try:
            o_dict = MySQLdb.connect( 
                dscrid[akdb]['host'],dscrid[akdb]['user'],dscrid[akdb]['pw'], 
                dscrid[akdb]['database'], 
                port=nport, 
                cursorclass=MySQLdb.cursors.DictCursor,
                charset=dscrid[akdb]['charset'], use_unicode=1)
        except:
            print_exc()
            print "#error: acces to %s/%s for user %s refused" % \
                (dscrid[akdb]['host'], dscrid[akdb]['database'], dscrid[akdb]['user'])
            sys.exit(1)
        # ddbaccess[akdb] = {'std':o_std.cursor(), 'dict':o_dict.cursor()}
        dscrid[akdb]['std']  = o_std.cursor()
        dscrid[akdb]['dict'] = o_dict.cursor()

    ddbaccess = dscrid # alias 
                           
    # print pprint.pformat(ddbaccess) 

    # 2.1 initiate scheme 
    oatransfact.init_scheme(dscheme, ddbaccess['src'])

    # 2.2 launching commande with option 
    # some meta-programming with caution 
    srealcmd = "do_%s" % scommand
    try:
        # meth is the named method "do_sthing" from object oatransfact
        meth = getattr(oatransfact, srealcmd)
    except:
        print_exc()
        sys.exit(1)
    # print string.join(dopt.keys()," ")     print dopt 
    if "chelp" in dopt.keys():
        # usage(scmd)
        print meth.__doc__
        sys.exit(1)
    else :  
        try:
            nret = meth(dopt, ddbaccess['src'],ddbaccess['dest']) # dscrid['src']) 
        except:
            print_exc()
            sys.exit(1)
    if nret < 0:
        usage(scmd)
        sys.exit(1)

    if scrofname != "" :
        try: 
            oscrof = open(scrofname, 'w')
        except:
            print "#error: cf output file %s cannot be written " % scrofname
        oscrof.write(pprint.pformat(dscrid)+"\n")
        oscrof.close()
    sys.exit(0) 

