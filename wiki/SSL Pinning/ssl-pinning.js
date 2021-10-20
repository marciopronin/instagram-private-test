const module = Process.getModuleByName('libliger.so');
const pattern = 'f8 b5 04 46 40 68 38 b1 20 6d 98 b1 00 20';
const target_addresses = Memory.scanSync(module.base, module.size, pattern);
Memory.patchCode(target_addresses[0].address, 8, code ​=> {
   const cw = new ThumbWriter(code);
   cw.putLdrRegU32('r0', 1);
   cw.putMovRegReg('pc', 'lr');
   cw.flush();
});