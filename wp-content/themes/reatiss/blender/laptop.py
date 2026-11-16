# Laptop shell for the home "Advanced Engineering Solutions" 3D scene
# (js/app-solutions-3d.js). MacBook Pro 14"-style unibody, silver, no logo.
#
#   blender -b --python blender/laptop.py
#   → img/home/solutions-3d/laptop.glb
#
# Three objects, each in the coordinates of the three.js group it goes into
# (three.js cm, y up; here Blender: x = x, y = -z, z = y — the glTF export
# turns it back):
#   shell_tub   bottom case, open at the top (laptop coords)
#   shell_deck  top case / keyboard deck (laptop coords — topCase)
#   shell_lid   lid on its hinge (lidPivot coords: z 0 = hinge, display at -y)
# Only the shapes come from here: the scene maps the materials by name
# (alu, alu_inner, black, trackpad, glass, hinge, rubber, port). Keys,
# backlight, speaker grilles and the screen stay in the scene code.
import math
import os

import bmesh
import bpy

OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "..", "img", "home", "solutions-3d", "laptop.glb")

# the scene's numbers (app-solutions-3d.js)
W = 31.2
D = 22.0
R = 0.95
TUB_H = 0.95
TOP_H = 0.24
DECK_Y = TUB_H + TOP_H
LD = D - 0.45
KB_Z = -3.6
PAD_Z = 5.9

CORNER_SEGS = 14

bpy.ops.wm.read_factory_settings(use_empty=True)

MATS = {}


def mat(name):
    if name not in MATS:
        m = bpy.data.materials.new(name)
        MATS[name] = m
    return MATS[name]


def plan_points(w, d, r, cx=0.0, cy=0.0):
    """Rounded rectangle outline (counter-clockwise), Blender XY."""
    r = min(r, w / 2 - 1e-4, d / 2 - 1e-4)
    pts = []
    corners = [(w / 2 - r, d / 2 - r, 0), (-w / 2 + r, d / 2 - r, 90), (-w / 2 + r, -d / 2 + r, 180), (w / 2 - r, -d / 2 + r, 270)]
    for x, y, a0 in corners:
        for i in range(CORNER_SEGS + 1):
            a = math.radians(a0 + 90 * i / CORNER_SEGS)
            pts.append((cx + x + r * math.cos(a), cy + y + r * math.sin(a)))
    return pts


def prism(name, w, d, r, z0, z1, cx=0.0, cy=0.0, bevel_top=0.0, bevel_bottom=0.0, material="alu"):
    """A rounded-rectangle slab from z0 to z1 with rounded top / bottom edges."""
    bm = bmesh.new()
    pts = plan_points(w, d, r, cx, cy)
    bot = [bm.verts.new((x, y, z0)) for x, y in pts]
    top = [bm.verts.new((x, y, z1)) for x, y in pts]
    bm.faces.new(list(reversed(bot)))
    bm.faces.new(top)
    n = len(pts)
    for i in range(n):
        j = (i + 1) % n
        bm.faces.new((bot[i], bot[j], top[j], top[i]))
    bm.edges.ensure_lookup_table()
    for z, off, segs in ((z1, bevel_top, 4), (z0, bevel_bottom, 6)):
        if off <= 0:
            continue
        ring = [e for e in bm.edges if all(abs(v.co.z - z) < 1e-6 for v in e.verts) and len(e.link_faces) == 2 and any(abs(f.normal.z) < 0.5 for f in e.link_faces)]
        bmesh.ops.bevel(bm, geom=ring, offset=off, segments=segs, profile=0.5, affect="EDGES", clamp_overlap=True)
    me = bpy.data.meshes.new(name)
    bm.to_mesh(me)
    bm.free()
    ob = bpy.data.objects.new(name, me)
    bpy.context.collection.objects.link(ob)
    me.materials.append(mat(material))
    return ob


def cut(target, cutter, material=None):
    """Boolean difference; the new faces take the cutter's material."""
    if material:
        cutter.data.materials.clear()
        cutter.data.materials.append(mat(material))
        if mat(material).name not in [m.name for m in target.data.materials if m]:
            target.data.materials.append(mat(material))
    mod = target.modifiers.new("cut", "BOOLEAN")
    mod.operation = "DIFFERENCE"
    mod.solver = "EXACT"
    mod.object = cutter
    mod.material_mode = "TRANSFER"
    bpy.context.view_layer.objects.active = target
    bpy.ops.object.modifier_apply(modifier=mod.name)
    bpy.data.objects.remove(cutter, do_unlink=True)


def faces_to(ob, test, material):
    """Assign a material to the faces whose normal passes test(normal)."""
    if material not in [m.name for m in ob.data.materials if m]:
        ob.data.materials.append(mat(material))
    idx = [m.name for m in ob.data.materials].index(material)
    for p in ob.data.polygons:
        if test(p.normal):
            p.material_index = idx


def join(objs, name):
    bpy.ops.object.select_all(action="DESELECT")
    for o in objs:
        o.select_set(True)
    bpy.context.view_layer.objects.active = objs[0]
    bpy.ops.object.join()
    ob = bpy.context.active_object
    ob.name = name
    ob.data.name = name
    return ob


def finish(ob):
    bpy.context.view_layer.objects.active = ob
    bpy.ops.object.select_all(action="DESELECT")
    ob.select_set(True)
    bpy.ops.object.shade_smooth_by_angle(angle=math.radians(35))
    # one empty-free object per part; clean doubles left by the booleans
    bm = bmesh.new()
    bm.from_mesh(ob.data)
    bmesh.ops.remove_doubles(bm, verts=bm.verts, dist=1e-5)
    bm.to_mesh(ob.data)
    bm.free()


def y_of(three_z):
    return -three_z


# ---------------------------------------------------------------- bottom case
tub = prism("shell_tub", W, D, R, 0.0, TUB_H, bevel_top=0.03, bevel_bottom=0.34)
pocket = prism("pocket", W - 0.64, D - 0.64, R - 0.32, 0.22, TUB_H + 1.0)
cut(tub, pocket, "alu_inner")

parts = [tub]
# rubber feet (four, round)
for sx in (-1, 1):
    for sy in (-1, 1):
        bpy.ops.mesh.primitive_cylinder_add(vertices=32, radius=0.5, depth=0.1, location=(sx * (W / 2 - 2.3), sy * (D / 2 - 2.0), -0.02))
        f = bpy.context.active_object
        f.data.materials.append(mat("rubber"))
        parts.append(f)


# ports on the sides: MagSafe, 2× USB-C, headphone jack | HDMI, USB-C, SD
def port(x_side, y, w, h, z=0.55, r=None):
    r = h / 2 if r is None else r
    ob = prism("port", 0.06, w, r, z - h / 2, z + h / 2, cx=x_side * (W / 2 - 0.025), cy=y)
    ob.data.materials.clear()
    ob.data.materials.append(mat("port"))
    return ob


parts += [
    port(-1, 6.2, 1.15, 0.3),  # MagSafe
    port(-1, 4.1, 0.85, 0.3),
    port(-1, 2.6, 0.85, 0.3),
    port(-1, -6.8, 0.34, 0.34),  # headphone jack (round)
    port(1, 5.6, 1.45, 0.4, r=0.1),  # HDMI
    port(1, 3.6, 0.85, 0.3),
    port(1, -5.8, 2.4, 0.16),  # SD slot
]
tub = join(parts, "shell_tub")
finish(tub)

# ---------------------------------------------------------------- top case
deck = prism("shell_deck", W, D, R, TUB_H, DECK_Y, bevel_top=0.06, bevel_bottom=0.02)
faces_to(deck, lambda n: n.z < -0.9, "alu_inner")  # the underside shows when it tips up
# keyboard well (black), keys sit on its floor
well = prism("well", 27.0, 11.6, 0.5, DECK_Y - 0.04, DECK_Y + 1.0, cy=y_of(KB_Z))
cut(deck, well, "black")
# trackpad: a fine groove round a flush glass-like panel
groove = prism("groove", 12.75, 7.95, 0.5, DECK_Y - 0.02, DECK_Y + 1.0, cy=y_of(PAD_Z))
cut(deck, groove, "black")
pad = prism("pad", 12.6, 7.8, 0.45, DECK_Y - 0.02, DECK_Y - 0.003, cy=y_of(PAD_Z), bevel_top=0.012, material="trackpad")
deck = join([deck, pad], "shell_deck")
finish(deck)

# ---------------------------------------------------------------- lid (lidPivot coords)
lid = prism("shell_lid", W, LD, R, 0.02, 0.36, cy=-LD / 2, bevel_top=0.1, bevel_bottom=0.03)
# display glass: black, edge to edge but a thin aluminium rim
# (depth order seen from the display side, -z here: notch 0.002 · the scene's
# screen image 0.007 · glass 0.012 — far enough apart not to z-fight)
glass = prism("glass", W - 0.24, LD - 0.24, R - 0.12, 0.012, 0.024, cy=-LD / 2, material="glass")
# the notch: hangs from the top bezel over the top edge of the screen
notch = prism("notch", 3.2, 0.5, 0.18, 0.002, 0.0045, cy=y_of(LD - 0.7), material="glass")
# hinge: dark bar along the back edge
bpy.ops.mesh.primitive_cylinder_add(vertices=40, radius=0.26, depth=W - 2 * R - 1.0, location=(0, 0.05, 0.0), rotation=(0, math.radians(90), 0))
hinge = bpy.context.active_object
hinge.data.materials.append(mat("hinge"))
lid = join([lid, glass, notch, hinge], "shell_lid")
finish(lid)

# ---------------------------------------------------------------- export
os.makedirs(os.path.dirname(OUT), exist_ok=True)
bpy.ops.export_scene.gltf(
    filepath=OUT,
    export_format="GLB",
    export_yup=True,
    export_apply=True,
    export_texcoords=False,
    export_normals=True,
    export_materials="EXPORT",
    export_cameras=False,
    export_lights=False,
)
for ob in (tub, deck, lid):
    print(ob.name, "verts", len(ob.data.vertices), "faces", len(ob.data.polygons), "mats", [m.name for m in ob.data.materials])
print("written", os.path.abspath(OUT), os.path.getsize(OUT), "bytes")
