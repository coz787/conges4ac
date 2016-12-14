# -*- mode: python; coding: utf-8 -*-  
'''cg_etl_lib.py: embodies TransformFactory and set of class built
to make the real transformation (designed as a Strategy dPattern) '''

import copy, sys, os, pprint, re, string, sys, time # , getopt
from decimal import Decimal 
from datetime  import date,timedelta,datetime
# import datetime  
from traceback import print_exc
import MySQLdb
dtimenow = datetime.now()
dnow = dtimenow.date() # datetime.date du jour 
nthisyear = dtimenow.year
# print dnow , nthisyear

def msd2string(omsdata):
    ''' mysql data to latin1 ''' 
    # print type(omsdata), omsdata.__class__, id(omsdata)
    if omsdata == None or omsdata == "" :
        sret =  '""' 
    elif isinstance(omsdata, Decimal)  :
        sret = "%-.2f" % omsdata
    elif isinstance(omsdata, long)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, int)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, date)  :
        # sret = "date"
        sret = omsdata.strftime('"%Y:%m:%d"') # :%X")
    elif isinstance(omsdata, unicode) :
        slatin1 = str(omsdata.encode('latin-1', 'ignore'))
#        slatin1 = string.replace(slatin1,'"','\"')
#        slatin1 = string.replace(slatin1,"'","\'")
#         sret = '"%s"' % re.escape(slatin1)
        slatin1 = string.replace(slatin1,'"',"'")
        sret = "%s" %  slatin1 # '"%s"' %  slatin1
    elif isinstance(omsdata, str):
        #omsdata = string.replace(omsdata,'"','\"')
        #omsdata = string.replace(omsdata,"'","\'")
        # sret = '"%s"' %  re.escape(omsdata) 
        slatin1 = string.replace(omsdata,'"',"'")
        sret = "%s" %  slatin1   # '"%s"' %  slatin1
    else:
        sret = "__unkowntype__%s" % type(omsdata) 
    # print sret 
    return sret 

# second version 
# dest_char_set supposed to be "utf8"
# src_char_set supposed to be "latin-1"
# but can be different according to migration parameter 

def py2msd(omsdata,dest_cs="utf8",src_cs="latin-1"):
    ''' python internal to mysql representation
we choose utf8 as default rep for unicode string, 
latin1 : among the possible value ''' 

    # print omsdata, type(omsdata), omsdata.__class__, id(omsdata)
    if omsdata == None or omsdata == "" :
        sret =  '""' 
    elif isinstance(omsdata, Decimal)  :
        sret = "%-.2f" % omsdata
    elif isinstance(omsdata, long)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, int)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, date)  :
        #     stime = omsdata.strftime('%HH:%MM:%SS')
        #     sday = omsdata.strftime('%Y:%m:%d') # :%X")
        # peut etre date ou datetime ; str retourne 
        # "2015-01-09" ou "2015-01-20 10:57:21"
        # print "py2msd::date ", sret 
        # sret = '"%s"' % str(omsdata)
        sret = omsdata.strftime('"%Y:%m:%d"')
    elif isinstance(omsdata, unicode) :
        # print "py2msd_char_set is %s for unicde data" % py2msd_char_set
        try:
            sdest = omsdata.encode(dest_cs, 'ignore')
            sdest = string.replace(sdest,'"',"'")
            sret = '"%s"' %  sdest
        except: 
            print type(omsdata) 
            ni = 0
            for achar in omsdata: 
                print ni,achar 
                ni += 1
            print_exc()
            sys.exit(1)
    elif isinstance(omsdata, str):
        omsdata = string.replace(omsdata,'"',"'")
        sdest = omsdata.decode(src_cs,'ignore').encode(dest_cs,'replace')
        sret = '"%s"' %  sdest   
        # sret = '"%s"' %  slatin1   # '"%s"' %  slatin1

    else:
        sret = "__unkowntype__%s" % type(omsdata) 
        # print sret 
    if sret == '""' :
        sret = "NULL" 
    return sret 

def py2msd_v1(omsdata,char_set="utf8"):
    ''' python internal to mysql representation
we choose utf8 as default rep for unicode string, 
latin1 : among the possible value ''' 

    # print omsdata, type(omsdata), omsdata.__class__, id(omsdata)
    if omsdata == None or omsdata == "" :
        sret =  '""' 
    elif isinstance(omsdata, Decimal)  :
        sret = "%-.2f" % omsdata
    elif isinstance(omsdata, long)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, int)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, date)  :
        #     stime = omsdata.strftime('%HH:%MM:%SS')
        #     sday = omsdata.strftime('%Y:%m:%d') # :%X")
        # peut etre date ou datetime ; str retourne 
        # "2015-01-09" ou "2015-01-20 10:57:21"
        sret = '"%s"' % str(omsdata)
        # print "py2msd::date ", sret 
    elif isinstance(omsdata, unicode) :
        # print "py2msd_char_set is %s for unicde data" % py2msd_char_set
        try:
            sdest = omsdata.encode(char_set, 'ignore')
            sdest = string.replace(sdest,'"',"'")
            sret = '"%s"' %  sdest
        except: 
            print type(omsdata) 
            ni = 0
            for achar in omsdata: 
                print ni,achar 
                ni += 1
            print_exc()
            sys.exit(1)
    elif isinstance(omsdata, str):
        omsdata = string.replace(omsdata,'"',"'")
        sdest = omsdata.decode('latin1','ignore').encode(char_set,'replace')
        sret = '"%s"' %  sdest   
        # sret = '"%s"' %  slatin1   # '"%s"' %  slatin1

    else:
        sret = "__unkowntype__%s" % type(omsdata) 
        # print sret 
    if sret == '""' :
        sret = "NULL" 
    return sret 

class OrderRecorder():
    def __init__(self,sfname):
        self.lorder = []
        try:
            self.oorfile = open(sfname, 'w')
        except:
            print "OrderRecorder %s cannot be written " % sfname
            sys.exit(1)
    def append(self,sorder):
        self.lorder.append(sorder)
        self.oorfile.write(sorder + "\n") 

    def get_order(self):
        return self.lorder 

class AbsTransformFactory:
    def __init__(self):
        self.dstrat = None 
        self.tclass = "_transform_" 
        self.name = "AbsTransformFactory"
        self.ldometh = [] 
        for sme in dir(self):
            if sme[0:3] == "do_" :
                 self.ldometh.append(sme[3:])
    def get_ldometh(self):
        return self.ldometh
    def get_meth(self):
        return ['dryrun','apply','realrun']
    def set_debug(self,ndbg):
        ndebug = ndbg 

    def std_scheme(self,dscheme) :
        self.dscheme = dscheme
        if not self.dscheme.has_key('tabpattern'):
            print "##error config" , 
            print "[%s] scheme definition has no ttabpattern data" % (self.name)
            sys.exit(1)
            
        self.ltable = [] 
        dtabpattern = self.dscheme['tabpattern']
        for ascheme in dtabpattern :
            self.ltable.append(ascheme[0]) 
        for ascheme in dtabpattern :
            amethod = ascheme[2] 
            if not self.dstrat.has_key(amethod): 
                # undefined meth for concrete class
                print "##error config" , 
                print "[%s] class has no transform method of name [%s]" % (self.name,amethod)
                sys.exit(1)

    def do_dryrun(self,dopt,osrc,odest):
        ''' option --sqlifile required '''
        if dopt.has_key('sqlifile'):
            ssqlifilename = dopt['sqlifile']
            self.oorec = OrderRecorder(ssqlifilename)
        else : 
            print "a valid sqlifile should be given using --sqlifile" 
            return -1 
        # print pprint.pformat(self.dscheme)
        for latable in self.dscheme['tabpattern'] :
            # print "%s:%s" % (latable[0],latable[2])
            if self.dstrat.has_key(latable[2]):
                if len(latable) >= 4:
                    dopt = latable[3]
                else:
                    dopt = {} 
                self.dstrat[latable[2]].run(
                    osrc,odest,latable[0],latable[1],self.oorec,dopt)
            else:
                print "method %s is unknown" % latable[2]
        return 1 

    def do_realrun(self,dopt,osrc,odest):
        # _tbc1
# lock tables conges_users write ;"unlock table ; " 
        sql_lock = u'lock tables %s ;' 
        sql_unlock = u'unlock tables ;'
        sql_setnames = "SET NAMES %s ; SET CHARACTER SET %s;" 
        self.do_dryrun(dopt,osrc,odest)
        ocdest = odest['std']
        try:
            slocko = sql_lock % (string.join(self.ltable,' write, ') + ' write' )
            ocdest.execute(slocko)
        except:
            print "sql_lock %s in error" % slocko
            print_exc()
            sys.exit(1)
        if False and odest.has_key('charset'):
            try:
                snameso = sql_setnames % ( odest['charset'],odest['charset'] )
                ocdest.execute(snameso)
                print "doing %s" % snameso
            except:
                print "sql_setnames %s in error" % snameso
                print_exc()
                sys.exit(1)

        for siorder in self.oorec.get_order():
            try:
                # soorder = siorder.decode(osrc['charset'],'ignore').encode(odest['charset'],'replace')
                # les ordres sont deja converties en chaine odest['charset'] 
                # par exemple utf8 par py2msd 
                # donc pas de conversion 
                soorder = siorder
                ocdest.execute(soorder)
            except:
                sys.stderr.write("sql_injection error on %s \n" % siorder)
                print_exc()
                # sys.exit(1)
        try:
            ocdest.execute(sql_unlock)
        except:
            print "sql_unlock in error" 
            print_exc()
            sys.exit(1)
        return 1 
# scorie
# uni_aorder = aorder.decode(odest['charset'], 'ignore')
                # uni_aorder = unicode(aorder, odest['charset'])
                # uni_aorder = aorder.encode(odest['charset'],'ignore')
                # print "doing %s " % aorder
                # uni_aorder = unicode(aorder, osrc['charset']).decode(odest['charset'],'ignore')
                # uni_aorder = unicode()
                # uni_aorder = unicode(aorder, odest['charset'])
                # ocdest.execute(uni_aorder)
                # ocdest.execute(aorder)
                # uni_aorder = aorder.decode(osrc['charset']).encode(odest['charset'])
                # print type(aorder),type(uni_aorder)
            
    def build_simple_user_list(self,ddbsrc):
        ''' set self.luser ordered to be used by other method '''
        ocursor = ddbsrc['std']
        # this 3 instructions only for _TU_ 
        self.luser0 = ['michele.mariaud'] 
        self.luser1 = ['annick.cadet', 
                      'carole.cesto', 
                      'gilles.paris', 
                      'isabelle.weiss', 
                      'marie-claude.potiron', 
                      'michele.mariaud', 
                      'natalia.de-castro', 
                      'nathalie.leonoff', 
                      'patricia.valentin', 
                      'reza.djafarian', 
                      'sandrine.horvath', 
                      'selma.ben-brahem', 
                      'stephanie.cilia', 
                      'sylvette.dufour', 
                      'sylvie.ledeux', 
                      'zelia.braz',
                      'didier.pavet',
                      'isabelle.pezzetta'
        ] 
        # return 
        self.luser = [] 
        try:
            ocursor.execute("select u_login from conges_users ;")
        except: 
            sys.stderr.write("select u_login from conges_users  ;\n") 
            print_exc()
            sys.exit(1)
        while 1  : 
            lrow = ocursor.fetchone()
            if lrow == None : 
                break
            self.luser.append(lrow[0]) 

                
class c4ac_2to3(AbsTransformFactory):
    def __init__(self):
        AbsTransformFactory.__init__(self)
        self.name = "c4ac_2to3"
        self.dstrat = { 'void_n_import' : VoidnImportKw() ,
#                        'void_n_selectiveimport' : VoidnSelectiveImport() ,
                        'void_n_import_gu' : VoidnImportGroupeUser() ,
                        'keep_n_update' : KeepnUpdate() }
    def init_scheme(self,dscheme,ddbsrc=None) :
        self.std_scheme(dscheme) 
    def nodo_apply(self,dopt,osrc,odest):
        pass 

class c4ac_2nsto3(AbsTransformFactory):
    def __init__(self):
        AbsTransformFactory.__init__(self)
        self.name = "c4ac_2nsto3"
        self.m_void_n_import_cartt  =  VoidnImportKwCartt()
        self.dstrat = { 'void_n_import' : VoidnImportKw() ,
                        'void_n_import_cartt' : self.m_void_n_import_cartt , 
#                        'void_n_selectiveimport' : VoidnSelectiveImport() ,
                        'void_n_import_gu' : VoidnImportGroupeUser() ,
                        'keep_n_update' : KeepnUpdate() }
    def init_scheme(self,dscheme,ddbsrc=None) :
        self.std_scheme(dscheme) 
        # building self.luser 
        self.build_simple_user_list(ddbsrc)        
        self.m_void_n_import_cartt.configure(self.luser)

    def nodo_apply(self,dopt,osrc,odest):
        pass 

class c4ac_2to2(AbsTransformFactory):
    def __init__(self):
        AbsTransformFactory.__init__(self)
        self.name = "c4ac_2to2"
        self.dstrat = { 'void_n_import' : VoidnImportKw() ,
                        'keep_n_update' : KeepnUpdate() }
    def init_scheme(self,dscheme,ddbsrc=None) :
        self.std_scheme(dscheme) 
    def nodo_apply(self,dopt,osrc,odest):
        pass 

class c4ac_2to2_filtered(AbsTransformFactory):
    def __init__(self):
        AbsTransformFactory.__init__(self)
        self.name = "c4ac_2to2_filtered"
        self.m_void_n_import_sel_user  = VoidnImportSelUser()
        self.m_void_n_import_sel_group = VoidnImportSelGroup()
        self.dstrat = { 'void_n_import' : VoidnImportKw() ,
                        'void_n_import_sel_user' : self.m_void_n_import_sel_user ,
                        'void_n_import_sel_group': self.m_void_n_import_sel_group ,
                        'keep_n_update' : KeepnUpdate() }
        
    def init_scheme(self,dscheme,ddbsrc=None) :
        self.std_scheme(dscheme) 
        try: 
            ogselection = dscheme['opt']['groupselection'] 
        except:
            print "##error config" , 
            print "[%s] class should define 'opt''groupselection'" % self.name
            sys.exit(1)
        self.build_selection_by_group(ogselection,ddbsrc)

    def build_selection_by_group(self,oselect,ddbsrc):
        ''' set self.dgid self.duser based on 
either a list of groupname 
either a string as a select order setting a list of groupname '''
        ocursor = ddbsrc['std']
        lgroupname = [] 
        self.dgid, self.duser = {} , {} 
        if isinstance(oselect,list):
            # print "is a list"
            lgroupname = oselect
        elif  isinstance(oselect,str):
            # print "is a string"
            try:
                ocursor.execute(oselect)
            except: 
                sys.stderr.write("select group with %s raises an error \n" % oselect) 
                print_exc()
                sys.exit(1)
            while 1  :
                lrow = ocursor.fetchone()
                if lrow == None : 
                    break
                lgroupname.append(msd2string(lrow[0]))
                #lgroupname.append(lrow[0])
        # we have a list of groupname in lgroupname
        # print pprint.pformat(lgroupname) 
        # we build a dict [g_gid] = g_groupename 
        try:
            ocursor.execute("select g_gid, g_groupename from conges_groupe;")
        except: 
            sys.stderr.write("select g_gid, g_groupename from conges_groupe\n") 
            print_exc()
            sys.exit(1)
        while 1  : 
            lrow = ocursor.fetchone()
            if lrow == None : 
                break
            # lagroup = map(msd2string, lrow) # print lagroup[1]
            lagroup = lrow 
            if lagroup[1] in lgroupname : # clumsy cumbersome but ... 
                self.dgid[lagroup[0]] = lagroup[1]
        # we build a dict [login] = 1 
        try:
            ocursor.execute("select gu_gid, gu_login from conges_groupe_users;")
        except: 
            sys.stderr.write("select gu_gid, gu_login from conges_groupe_users;\n") 
            print_exc()
            sys.exit(1)
        while 1  : 
            lrow = ocursor.fetchone()
            if lrow == None : 
                break
            # lagroupuser = map(msd2string, lrow)
            lagroupuser = lrow
            if self.dgid.has_key(lagroupuser[0]): # this group is included
                self.duser[lagroupuser[1]] = 1 # overkill 
        # print pprint.pformat(oselect)
        # print pprint.pformat(lgroupname)
        # print pprint.pformat(self.dgid) 
        # print pprint.pformat(self.duser)
        # we include systematically 'admin' and 'conges'
        self.duser['admin'] = 1 
        self.duser['conges'] = 1 
        self.m_void_n_import_sel_user.configure(self.duser)
        self.m_void_n_import_sel_group.configure(self.dgid)
        # print self.dgid, self.duser
        # print "premattured end." 
        # sys.exit(0)
    
class AbsTransform:
    def __init__(self):
        pass
    def truncate(self,stablename):
        sql_truncate = "truncate %s ;" %  stablename 
        # si mysql < 5.0 
        # sql_truncate = "delete from %s ;" % stablename
        return sql_truncate
    def isvalid_primary_key(self, drow, lpkey):
        ''' determine si la cle primaire de la donnes est non nul'''
        spkey = "" 
        for akeyfield in lpkey :
            try:
                spkey += "%s" % drow[akeyfield]
            except:
                pass 
        # print "isvalid_primary_key %s" % spkey 
        if spkey == "" :
            return False
        else :
            return True 
    def has_nonull(self, drow, lfield):
        ''' determine si un des champs lfield est null ou ""
si 1 des champs est null, ret False
si aucun est null, ret True  '''
        #print "has_nonull %s" % lfield, 
        bhasnn = True
        for afield in lfield :
            if not drow[afield] : # cas None 
                bhasnn = False
                break 
            sfield = "%s" % drow[afield]
            #if sfield == "None":
            #    print drow[afield] # sfield, 
            if sfield == "" : # or sfield == "None" :
                bhasnn = False
                break 
        #print 
        return bhasnn
    def isvalid_row(self,stable,drow,lpkey,opt):
        bvalid = False 
        if opt.has_key('notnull'):
            lfieldnotnull = opt['notnull'] 
        else:
            lfieldnotnull = None 
        if lfieldnotnull :
            b_cond_nullfield = self.has_nonull(drow,lfieldnotnull)
        else : 
            b_cond_nullfield = True 
        b_cond_prim_key = self.isvalid_primary_key(drow, lpkey)    
        # print lfieldnotnull, b_cond_nullfield            
        # default py2msd conversion are used since it is only for printing 
        if not b_cond_prim_key :
            print "%s table row [%s] has an invalid prim key item" % \
                (stable,string.join(map(py2msd,drow.values()),','))
        elif not b_cond_nullfield : 
            print "%s table row [%s] has null field among those [%s]" % \
                (stable,string.join(map(py2msd,drow.values()),','),
                 string.join(lfieldnotnull,','))
        else:
            bvalid = True 
        return bvalid 

    def insert_all_dict_noorder(self,stablename,drow):
        ''' produit ordre sql insert into en mode dict '''
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        lk, lv = [], []  
        for (k,v) in drow.items() : 
            lk.append(k)
            lv.append(v)
        return sql_insert_all_dict % (
            stablename,
            string.join(lk,','),
            string.join(map(lambda p: py2msd(p,self.odest['charset'],self.osrc['charset']), lv),','))
    def insert_all_dict(self,stablename,drow):
        ''' produit ordre sql insert into en mode dict 
cle en ordre alphabetic '''
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        lk = drow.keys() 
        lk.sort() 
        lv = []
        for k in lk :
            lv.append(drow[k])
        return sql_insert_all_dict % (
            stablename,
            string.join(lk,','),
            string.join(map(lambda p: py2msd(p,self.odest['charset'],self.osrc['charset']), lv),','))

    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        self.osrc = osrc 
        self.odest = odest
        self.stable = stable
        self.lpkey = lpkey 
        self.opt = opt 
    def print_table_struct(self,osrc,odest,stable):
        ssqldesc = "SHOW COLUMNS FROM %s.%s ; "
        sqlorder = ssqldesc % (osrc['database'],stable)
        ocursor = osrc['std']
        try:
            ocursor.execute(sqlorder)
        except:
            print_exc()
            print "%s raises an error  " % sqlorder
            sys.exit(1)
        while 1 :
            row = ocursor.fetchone()
            if row == None :
                break
            print "%s;%s" % (stable, string.join(map(msd2string, row) ,";")) 
    sdoc ='''
signification des champs 
table; champs; type; NO (NOT NULL) ; PRI KEY; val defaut ; 
conges_groupe;g_gid;int(11);NO;PRI;"";auto_increment
conges_groupe;g_groupename;varchar(50);NO;"";"";""
conges_groupe;g_comment;varchar(250);YES;"";"";""
conges_groupe;g_double_valid;enum('Y','N');NO;"";N;""
conges_periode;p_login;varbinary(256);NO;"";"";""
conges_periode;p_date_deb;date;NO;"";0000-00-00;""
conges_periode;p_demi_jour_deb;enum('am','pm');NO;"";am;""
conges_periode;p_date_fin;date;NO;"";0000-00-00;""
conges_periode;p_demi_jour_fin;enum('am','pm');NO;"";pm;""
conges_periode;p_nb_jours;decimal(5,2);NO;"";0.00;""
conges_periode;p_commentaire;varchar(50);YES;"";"";""
conges_periode;p_type;int(2) unsigned;NO;"";1;""
conges_periode;p_etat;enum('ok','valid','demande','ajout','refus','annul');NO;"";demande;""
conges_periode;p_edition_id;int(11);YES;"";"";""
conges_periode;p_motif_refus;varchar(110);YES;"";"";""
conges_periode;p_date_demande;datetime;YES;"";"";""
conges_periode;p_date_traitement;datetime;YES;"";"";""
conges_periode;p_num;int(5) unsigned;NO;PRI;"";auto_increment
conges_periode;p_fermeture_id;int(11);YES;"";"";""

'''
    def get_table_struct(self,osrc,odest,stable):
        ''' lit la struct de la table et batit self.dtabstruc  
[champ]={'type':s,n,e , 'notnull': NO, 'defaut':<val defaut> } '''
        ssqldesc = "SHOW COLUMNS FROM %s.%s ; "
        sqlorder = ssqldesc % (osrc['database'],stable)
        ocursor = osrc['std']
        self.dtabstruc = {} 
        try:
            ocursor.execute(sqlorder)
        except:
            print_exc()
            print "%s raises an error  " % sqlorder
            sys.exit(1)
        while 1 :
            lrow = ocursor.fetchone()
            if lrow == None :
                break
            sfield = lrow[0]
            stype = lrow[1]
            stype0 = stype[0] # 1er lettre 
            # tobe continued if stype0 ==   

class VoidnImportKw(AbsTransform): 
    '''delete all record and import element from source that correspond
to optional selection criteria , by keyword '''
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        # self.print_table_struct(osrc,odest,stable)
        orecorder.append(self.truncate(stable)) 
        sql_select_star = "select * from %s where %s ;"
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        ocursor = osrc['dict']
        # where clause has an option 
        if opt.has_key('selection'):
            swherec = opt['selection'] 
        else :
            swherec = "1" # by default 
        ocursor.execute(sql_select_star  % (stable,swherec) )
        nline = 0 # tools to only execute here 25 order  
        while 1 : # and nline < 5 :
            drow = ocursor.fetchone()
            if drow == None : 
                break
            bvalid_row = self.isvalid_row(stable,drow,lpkey,opt)
            # if drow['p_etat'] == "hp" : 
            #     print drow 
            if bvalid_row: 
                orecorder.append(self.insert_all_dict(stable,drow))
            nline += 1 

class VoidnImportKwCartt(AbsTransform): 
    '''delete all record and import element from source 
WITH <<subtle correction of artt data>> '''
    def configure(self,luser):
        self.luser = luser 
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        # self.print_table_struct(osrc,odest,stable)
        orecorder.append(self.truncate(stable)) 
        # _specific_artt stable = conges_artt 
        sql_select_star = "select * from %s where a_login='%s' ORDER BY a_date_fin_grille ASC ;"
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        ocursor = osrc['dict']
        # no other where clause
        # required constant 
        sendoftime = "9999-12-31" 
        dendoftime = date(9999,12,31)
        doneday = timedelta(days=1)

        # demptyartt to be cloned import copy
        # dartt = copy.copy(demptyartt)   or copy.deepcopy(demptyartt) 
        demptyartt = {'a_date_debut_grille': None,
                      'a_date_fin_grille': None,
                      'a_login': None,
                      'sem_imp_di_am': None,
                      'sem_imp_di_pm': None,
                      'sem_imp_je_am': None,
                      'sem_imp_je_pm': None,
                      'sem_imp_lu_am': None,
                      'sem_imp_lu_pm': None,
                      'sem_imp_ma_am': None,
                      'sem_imp_ma_pm': None,
                      'sem_imp_me_am': None,
                      'sem_imp_me_pm': None,
                      'sem_imp_sa_am': None,
                      'sem_imp_sa_pm': None,
                      'sem_imp_ve_am': None,
                      'sem_imp_ve_pm': None,
                      'sem_p_di_am': None,
                      'sem_p_di_pm': None,
                      'sem_p_je_am': None,
                      'sem_p_je_pm': None,
                      'sem_p_lu_am': None,
                      'sem_p_lu_pm': None,
                      'sem_p_ma_am': None,
                      'sem_p_ma_pm': None,
                      'sem_p_me_am': None,
                      'sem_p_me_pm': None,
                      'sem_p_sa_am': None,
                      'sem_p_sa_pm': None,
                      'sem_p_ve_am': None,
                      'sem_p_ve_pm': None}
        # print pprint.pformat(self.luser)
        for sauser in self.luser : 
            ocursor.execute(sql_select_star  % (stable,sauser) )
            self.lartt , self.newlartt = [], [] 
            while 1 : # we store all row in a list 
                drow = ocursor.fetchone()
                if drow == None : 
                    break
                self.lartt.append(drow)
                # print pprint.pformat(drow)
                # return # prematuredend 
            nrow = len(self.lartt) 
            if nrow > 0 : # some artt exists 
                nlast = nrow - 1 
                # if (self.lartt[nlast]['a_date_fin_grille'] == sendoftime ) : 
                idrow = 0 
                dpreviousrow = None 
                while idrow < nrow : 
                    dcrow = self.lartt[idrow]
                    if dcrow['a_date_debut_grille'] < dcrow['a_date_fin_grille'] :
                        
                        if dpreviousrow : 
                            dcrow['a_date_debut_grille'] = \
                               dpreviousrow['a_date_fin_grille'] + doneday
                        dpreviousrow = dcrow 
                        dnewartt = None 
                        if idrow == nlast : 
                            if dcrow['a_date_fin_grille'] != sendoftime :
                                if dcrow['a_date_fin_grille'] < dnow :
                                    dnewartt = copy.copy(demptyartt)
                                    dnewartt['a_login'] = sauser 
                                    dnewartt['a_date_fin_grille'] = dendoftime
                                    dnewartt['a_date_debut_grille'] = \
                                       dcrow['a_date_fin_grille'] + doneday
                                else: # on force a endoftime 
                                    dcrow['a_date_fin_grille'] = sendoftime
                        # we add the current row
                        self.newlartt.append(dcrow)
                        if dnewartt : # if exists we add the new row
                            self.newlartt.append(dnewartt)
                    else : # otherwise item is discarded  
                        print "# artt schema supprime %s, %s, %s " % \
                            (sauser,dcrow['a_date_debut_grille'], dcrow['a_date_fin_grille'] )
                    idrow += 1
                #end while idrow < nrow 
            else : # no artt we create one
                print "# artt creation for %s" % sauser 
                dnewartt = copy.copy(demptyartt)
                dnewartt['a_login'] = sauser
                dnewartt['a_date_fin_grille'] = dendoftime
                dnewartt['a_date_debut_grille'] = date(nthisyear, 1, 1)
                self.newlartt.append(dnewartt)
            # we have a ready-to-inject list of artt 
            for danewrow in self.newlartt:
                orecorder.append(self.insert_all_dict(stable,danewrow))
            #ending for sauser in self.luser :    
        # theend
        return 

class VoidnImportSelUser(AbsTransform): 
    '''delete all record and import element from source that correspond
to a valid list of user '''
    def configure(self,duser):
        self.dseluser = duser 
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        orecorder.append(self.truncate(stable))
        sql_select_star = "select * from %s ;"
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        ocursor = osrc['dict'] # use of dict cursor 
        # required definition of a key to make filtering
        if opt.has_key('key'):
            skfield = opt['key'] 
        else :
            sys.stderr.write("##error config ; a key filed should be defined" ) 
            sys.exit(1)
        ocursor.execute(sql_select_star  % stable )
        while 1 :
            drow = ocursor.fetchone()
            if drow == None : 
                break
            bvalid_row = self.isvalid_row(stable,drow,lpkey,opt)
            if bvalid_row: 
                # selection 
                if self.dseluser.has_key(drow[skfield]) : # ok then 
                    orecorder.append(self.insert_all_dict(stable,drow))
                #if drow[skfield] == 'admin' :
                #    print drow 
# sql order scheme : 
# insert into conges_users (u_resp_login,u_is_gest,u_passwd,u_nom,u_prenom,u_see_all,u_is_resp,u_quotite,u_is_admin,u_login,u_has_int_calendar,u_email) values ("jean-pierre.desbenoit","N","d41d8cd98f00b204e9800998ecf8427e","LEBRETON","Patrick","N","N",100,"N","patrick.lebreton","Y","patrick.lebreton@aviation-civile.gouv.fr") ;

class VoidnImportSelGroup(AbsTransform): 
    '''delete all record and import element from source that correspond
to a valid list of group '''
    def configure(self,dgid):
        self.dselgid = dgid 
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        orecorder.append(self.truncate(stable) )
        sql_select_star = "select * from %s ;"
        sql_insert_all_dict = "insert into %s (%s) values (%s) ;"
        ocursor = osrc['dict'] # use of dict cursor 
        # required definition of a key to make filtering
        if opt.has_key('key'):
            skfield = opt['key'] 
        else :
            sys.stderr.write("##error config ; a key filed should be defined" ) 
            sys.exit(1)
        ocursor.execute(sql_select_star  % stable )
        while 1 :
            drow = ocursor.fetchone()
            if drow == None : 
                break
            # print "VoidnImportSelGroup ", drow[skfield]
            bvalid_row = self.isvalid_row(stable,drow,lpkey,opt)
            if bvalid_row: 
                # selection 
                if self.dselgid.has_key(drow[skfield]) : # ok then 
                    # print "imported " 
                    orecorder.append(self.insert_all_dict(stable,drow))

class VoidnSelectiveImport(AbsTransform): 
    '''delete all record and import element from source that correspond
to optional selection criteria '''
    def run(self):
        pass 

class VoidnImportGroupeUser(AbsTransform): 
    '''delete all record and adapt input to output'''
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        #print stable,lpkey
        orecorder.append(self.truncate(stable))
        sql_select_star = "select * from %s ;"
        sql_insert_all = "insert into %s values (%s) ;"
        ocursor = osrc['std']
        ocursor.execute(sql_select_star  % stable) 
        while 1 :
            row = ocursor.fetchone()
            if row == None : 
                break
            # SPECIF 
            newrow = list(row)
            newrow.append('membre')
            orecorder.append(sql_insert_all % (
                    stable, 
                    string.join(map(lambda p: py2msd(p,self.odest['charset'],self.osrc['charset']), newrow),',')))
            # string.join(map(py2msd, newrow),',')))

# update conges_artt set a_date_debut_grille = '2015-01-01' where a_login='catherine.retailleau' and a_date_fin_grille = '9999-12-31'
class KeepnUpdate(AbsTransform): 
    '''browse all element from source and 
if existing (test on primary key) , update with new values,
if non existing insert it as a new record '''
    def run(self,osrc,odest,stable,lpkey,orecorder,opt):
        # common thing from parent class 
        AbsTransform.run(self,osrc,odest,stable,lpkey,orecorder,opt)
        sql_select_star = "select * from %s where %s ;"
        sql_select_wk = "select * from %s where %s ;" 
        sql_insert_all = "insert into %s values (%s) ;"
        sql_update = "update %s set %s where %s ;" 
        ocsrc = osrc['dict']
        # where clause has an option 
        if opt.has_key('selection'):
            swsrc = opt['selection'] 
        else: # or "1" to select * 
            swsrc = "1" 
        ocsrc.execute(sql_select_star  % (stable,swsrc) )

        while 1  :
            drow = ocsrc.fetchone()
            if drow == None : 
                break        

            ocdest = odest['std'] 
            swc = self.get_where(drow,lpkey)
            # print sql_select_wk % (stable,swc) 
            ocdest.execute(sql_select_wk % (stable,swc))
            if ocdest.fetchone() : # found existing , so update is required 
                ssc = self.get_set(drow,lpkey)
                sorder = sql_update % (stable,ssc,swc)
            else: # insert is required 
                sinto, svv = self.get_field_val(drow,stable)
                # sinto = "%s (%s)" % (stable,sff)
                svalues = svv 
                sorder = sql_insert_all % (sinto,svalues)
                           
            orecorder.append(sorder)
            #print drow 
            #print self.get_where(drow,lpkey)
            #print self.get_set(drow,lpkey)

    def get_set(self,drow,lpkey):
        ssetc = "" 
        for akey in drow.keys():
            if akey not in lpkey :
                asc = "%s=%s" % (akey,py2msd(drow[akey],self.odest['charset'],self.osrc['charset']))
                if ssetc == "" :
                    ssetc = asc
                else:
                    ssetc += ", %s" % asc
        return ssetc

    def get_where(self,drow,lpkey):
        swherec = "" 
        for akey in lpkey:
            if drow.has_key(akey) :
                awc = "%s=%s" % (akey,py2msd(drow[akey],self.odest['charset'],self.osrc['charset']))
                if swherec == "" :
                    swherec = awc 
                else :
                    swherec += "and %s" % awc 
            else :
                print "#warning %s key is unknown from row" % akey

        return swherec

    def get_field_val(self,drow,stab):
        ''' retourne champ sinto et sval tel qu'attendu
par insert into %s values (%s) '''
        sfield, sval = "", ""
        lkfield = drow.keys()
        lkfield.sort()
        for akey in lkfield :
            if sfield  == "" :
                sfield = akey 
            else :
                sfield += ", %s" % akey
            if sval  == "" :
                sval = py2msd(drow[akey],self.odest['charset'],self.osrc['charset']) 
            else :
                sval += ", %s" % py2msd(drow[akey],self.odest['charset'],self.osrc['charset'])
        sinto = "%s (%s)" % (stab,sfield)
        return sinto, sval
 
if __name__ == '__main__':
    # developper module d'autotest minimal
    oat = AbsTransform()
    
    dmyrow = {'key1': 'val1','key2': 'val2','key3': 'val3' }
    # print oat.insert_all_dict("dummytable",dmyrow)
    uni_lat1 = u"âäàéèëê"  # chaine unicode en latin 1
    print "uni_lat1 en utf8 ", py2msd( uni_lat1 )
    sys.exit(1)
